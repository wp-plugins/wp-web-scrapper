<?php
/*
Plugin Name: WP Web Scrapper
Plugin URI: http://webdlabs.com/projects/wp-web-scraper/
Description: An easy to implement web scraper for WordPress. Display realtime data from any websites directly into your posts, pages or sidebar.
Author: Akshay Raje
Version: 1.5
Author URI: http://webdlabs.com

*/

define('wpws_ABSPATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );
define('wpws_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );
include_once (dirname (__FILE__)."/tinymce/tinymce.php");

if(get_option('wpws_sc_posts') == 1) add_shortcode('wpws', 'wpws_shortcode');
if(get_option('wpws_sc_sidebar') == 1) add_filter('widget_text', 'do_shortcode');
add_action('admin_menu', 'wpws_settings_page');
register_activation_hook( __FILE__, 'wpws_on_activation');

foreach (glob(dirname(__FILE__)."/modules/*.php") as $mod) {
    require_once($mod);
}

function wpws_getDirectorySize($path) {
	$totalsize = 0;
	$totalcount = 0;
	$dircount = 0;
	if ($handle = opendir ($path)) {
		while (false !== ($file = readdir($handle))) {
			$nextpath = $path . '/' . $file;
			if ($file != '.' && $file != '..' && !is_link ($nextpath)) {
				if (is_dir ($nextpath)) {
					$dircount++;
					$result = wpws_getDirectorySize($nextpath);
					$totalsize += $result['size'];
					$totalcount += $result['count'];
					$dircount += $result['dircount'];
				}
				elseif (is_file ($nextpath)) {
					$totalsize += filesize ($nextpath);
					$totalcount++;
				}
			}
		}
	}
	closedir ($handle);
	$total['size'] = $totalsize;
	$total['count'] = $totalcount;
	$total['dircount'] = $dircount;
	return $total;
}

function wpws_sizeFormat($size) {
	if($size<1024)	return $size.__(" bytes");
	else if($size<(1024*1024)) {
		$size=round($size/1024,1);
		return $size.__(" KB");
	}
	else if($size<(1024*1024*1024)) {
		$size=round($size/(1024*1024),1);
		return $size.__(" MB");
	}
	else {
		$size=round($size/(1024*1024*1024),1);
		return $size.__(" GB");
	}
}

function wpws_curl($url, $agent, $timeout, $return = true, $postargs = '') {
	error_reporting(1);
	$ch = curl_init();
	if (!$ch) {
		if (function_exists('file_get_contents')) {
			ini_set('default_socket_timeout', $timeout * 60);
			$html = file_get_contents($url);
			if ($html === false) {
				$curl[0] = false;
				$curl[1] = 'Could not initialize cURL and file_get_contents()';			
			} else {
				$curl[0] = true;
				$curl[1] = $html;			
			}
		}
	} else {
		if ($postargs != '') {
			curl_setopt($ch, CURLOPT_POST ,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$postargs);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$html = curl_exec($ch);
		if (empty($html)) {
			$curl[0] = false;
			$curl[1] = curl_error($ch);
			curl_close($ch); 
		} else {
			$curl[0] = true;
			if($return) $curl[1] = $html;		
			curl_close($ch);
		}
	}
	return $curl;	
}

function wpws_get_content($url = '', $postargs = '', $selector = '', $clear = '', $replace = '', $replace_text = '', $basehref = '', $output_format = '', $cache_timeout = '', $curl_agent = '', $curl_timeout = '', $curl_error = '') {
	
	if($cache_timeout == '') $cache_timeout = get_option('wpws_cache_timeout');
	if($output_format == '') $output_format = 'text';
	if($curl_agent == '') $curl_agent = get_option('wpws_curl_agent');
	if($curl_timeout == '') $curl_timeout = get_option('wpws_curl_timeout');
	if($curl_error == '') $curl_error = get_option('wpws_curl_error');	
	
	if($url == '' || $selector == '') {
		if($curl_error == '1') {return 'Required params missing';}
		elseif($curl_error == '0') {return false;} 
		else {return $curl_error;}		
	} else {
		$cache_file = dirname(__FILE__).'/cache/'.urlencode(str_replace('http://','',$url));
		if ($postargs != '') {
			$cache_file = $cache_file.urlencode('?'.$postargs);
		}		
		$cache_file_status = file_exists($cache_file);
		$timestamp_id = '<!--wpws_timestamp-->';
		if($cache_file_status) {
			$wpws_timestamp = explode($timestamp_id, file_get_contents($cache_file));
			$cache_file_ctime = $wpws_timestamp[1];
			$cache_status = (time() - $cache_file_ctime) < ($cache_timeout * 60);
		} else {$cache_status = false;}
		if($cache_status) {
			return wpws_parse_byselector($wpws_timestamp[0], $selector, $clear, $replace, $replace_text, $basehref, $output_format);		
		} else {
			$scrap = wpws_curl(html_entity_decode($url), $curl_agent, $curl_timeout, true, $postargs);
			if($scrap[0]) {
				file_put_contents($cache_file, $scrap[1].$timestamp_id.time());
				return wpws_parse_byselector($scrap[1], $selector, $clear, $replace, $replace_text, $basehref, $output_format);
			} else {
				if($curl_error == '1') {return $scrap[1];}
				elseif($curl_error == '0') {return false;} 
				elseif($curl_error == 'cache' && $cache_file_status) {
					$wpws_timestamp = explode($timestamp_id, file_get_contents($cache_file));
					return wpws_parse_byselector($wpws_timestamp[0], $selector, $clear, $replace, $replace_text, $basehref, $output_format);	
				} 
				else {return $curl_error;}
			}
		}
	}
}

function wpws_parse_byselector($scrap, $selector, $clear, $replace, $replace_text, $basehref, $output_format) {
	require_once('phpQuery.php');
	$doc = phpQuery::newDocumentHTML($scrap);
	phpQuery::selectDocument($doc);	
	if($output_format == 'text') {$output = pq($selector)->text();}
	elseif($output_format == 'html') {$output = pq($selector)->html();}
	if($clear != '') {$output = preg_replace($clear, '', $output);}
	if($replace != '') {$output = preg_replace($replace, $replace_text, $output);}
	if($basehref != '') {$output = str_replace('"/','"'.$basehref.'/',$output);}
	return $output;	
}

function wpws_shortcode($atts) {
	extract(shortcode_atts(array('url' => '', 'postargs' => '', 'selector' => '', 'clear' => '', 'replace' => '', 'replace_text' => '', 'basehref' => '', 'output' => 'text', 'cache' => get_option('wpws_cache_timeout'), 'agent' => get_option('wpws_curl_agent'), 'timeout' => get_option('wpws_curl_timeout'), 'error' => get_option('wpws_curl_error')), $atts));
	$url = urldecode($url);
	$postargs = str_replace('#038;','',urldecode($postargs));
	if(preg_match('/___(.*)___/',$url,$url_matches)){
		$url = preg_replace('/___(.*)___/',$_REQUEST[$url_matches[1]],$url);
	}	
	if(preg_match('/___(.*)___/',$postargs,$args_matches)){
		$postargs = preg_replace('/___(.*)___/',$_REQUEST[$args_matches[1]],$postargs);
	}		
	return wpws_get_content($url, $postargs, $selector, $clear, $replace, $replace_text, $basehref, $output, $cache, $agent, $timeout, $error);
}

function wpws_settings_page(){
	add_options_page('WP Web Scraper Settings', 'WP Web Scraper', 8, __FILE__, 'wpws_settings_html');
}

function wpws_on_activation(){
	add_option('wpws_sc_posts', 1);
	add_option('wpws_sc_sidebar', 1);
	add_option('wpws_curl_error', 0);
	add_option('wpws_curl_agent', "WPWS bot (".get_bloginfo('url').")");
	add_option('wpws_curl_timeout', 1);
	add_option('wpws_cache_timeout', 60);
}

function wpws_settings_html(){
$cache_root = dirname(__FILE__).'/cache';
$size_array = wpws_getDirectorySize($cache_root);
?>
<script type="text/javascript">
var popUpWin=0;
function clear_cache(){
	jQuery('#wpws_cache_status').load('../wp-content/plugins/wp-web-scrapper/wpws_cache_clear.php', {count: <?php echo $size_array['count'];?>}, function(){
   		jQuery('#wpws_cache_status').addClass('fade');
	});
}
</script>
<div class="wrap">

	<?php if(function_exists(screen_icon)) {screen_icon();} ?>
	<h2><?php _e('WP Web Scraper Settings'); ?></h2>
	<small>Powered by <a href="http://webdlabs.com" target="_blank">webdlabs.com</a>. Please <a href="http://webdlabs.com/projects/donate/" target="_blank">donate (by paypal)</a> if you found this useful.</small>

	<form method="post" action="options.php" id="wpws_options">
	<?php wp_nonce_field('update-options'); ?>
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label><?php _e('WP Web Scraper Shortcodes') ?></label></th>
			<td><fieldset>
			<label for="wpws_sc_posts">
			<input name="wpws_sc_posts" type="checkbox" id="wpws_sc_posts" value="1" <?php checked('1', get_option('wpws_sc_posts')); ?> />
			<?php _e('Enable shortcodes for posts and pages') ?></label>
			<br />
			<label for="wpws_sc_sidebar">
			<input name="wpws_sc_sidebar" type="checkbox" id="wpws_sc_sidebar" value="1" <?php checked('1', get_option('wpws_sc_sidebar')); ?> />
			<?php _e('Enable shortcodes in sidebar text widget') ?></label>
			</fieldset></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label><?php _e('cURL error handlng options') ?></label></th>
			<td>
			<select name="wpws_curl_error" id="wpws_curl_error" style="width:325px" class="regular-text code" >
				<option value="0"<?php selected('0', get_option('wpws_curl_error')); ?>>Fail silently (diplays blank string on failure)</option>
				<option value="1"<?php selected('1', get_option('wpws_curl_error')); ?>>Display error (can be used while debuging)</option>
				<option value="cache"<?php selected('cache', get_option('wpws_curl_error')); ?>>Force display cache (even if expired)</option>
			</select>
			<!--<input name="wpws_curl_error" type="checkbox" id="wpws_curl_error" value="1" <?php checked('1', get_option('wpws_curl_error')); ?> />-->
			<span class="setting-description"><?php _e('Default cURL error handling. Fail silently, display error or display expired cache.') ?></span>
			</td>
		</tr>		
		<tr valign="top">
			<th scope="row"><label><?php _e('cURL useragent string') ?></label></th>
			<td>
			<input name="wpws_curl_agent" type="text" id="wpws_curl_agent" value="<?php form_option('wpws_curl_agent'); ?>" class="regular-text code" />
			<span class="setting-description"><?php _e('Default useragent header to identify yourself when crawling sites. Read more.') ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label><?php _e('cURL timeout (in seconds)') ?></label></th>
			<td>
			<input name="wpws_curl_timeout" type="text" id="wpws_curl_timeout" value="<?php form_option('wpws_curl_timeout'); ?>" class="small-text code" />
			<span class="setting-description"><?php _e('Default timeout interval in seconds for cURL. Larger interval might slow down your page.') ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label><?php _e('Cache timeout (in minutes)') ?></label></th>
			<td>
			<input name="wpws_cache_timeout" type="text" id="wpws_cache_timeout" value="<?php form_option('wpws_cache_timeout'); ?>" class="small-text code"/>
			<span class="setting-description"><?php _e('Default timeout in minutes for cached webpages.') ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label><?php _e('Cache management') ?></label></th>
			<td>
			<input type="button" name="wpws_cache_clear" id="wpws_cache_clear" value="<?php _e('Clear Cache') ?>" class="button-secondary" onclick="clear_cache(); return false;"/><br />
			<span class="setting-description" id="wpws_cache_status"><?php _e('Your cache currently has '.($size_array['count'] - 3).' files occuping '.wpws_sizeFormat($size_array['size'] - 296).' of space.') ?></span>
			</td>
		</tr>
		</table>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="wpws_sc_posts,wpws_sc_sidebar,wpws_curl_error,wpws_curl_agent,wpws_curl_timeout,wpws_cache_timeout" />
	<p class="submit">
	<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>
	</form>

</div>
<?php
}
?>