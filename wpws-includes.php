<?php
/*
 * Collection of internal library functions
*/

define('wpws_ABSPATH', WP_PLUGIN_DIR.'/'.plugin_basename( dirname(__FILE__) ).'/' );
define('wpws_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );

require_once(dirname (__FILE__).'/tinymce/tinymce.php');

foreach (glob(dirname(__FILE__).'/modules/*.php') as $mod) {
    require_once($mod);
    if((get_option('wpws_sc_posts') == 1) && (function_exists("wpws_shortcode_$wpws_mod_name")))
        add_shortcode("wpws_$wpws_mod_name", "wpws_shortcode_$wpws_mod_name");
}

if(get_option('wpws_sc_posts') == 1) 
    add_shortcode('wpws', 'wpws_shortcode');
if(get_option('wpws_sc_sidebar') == 1) 
    add_filter('widget_text', 'do_shortcode');

function wpws_register_activation_hook() {
    global $wpdb;

    add_option('wpws_sc_posts', 1);
    add_option('wpws_sc_sidebar', 1);
    add_option('wpws_on_error', 0);
    add_option('wpws_user_agent', "WPWS bot (".get_bloginfo('url').")");
    add_option('wpws_timeout', 1);
    add_option('wpws_cache', 60);
    add_shortcode('wpws', 'wpws_shortcode');
    add_filter('widget_text', 'do_shortcode');

    $wpws_scrapmeta = $wpdb->prefix.'wpws_scrapmeta';
    if($wpdb->get_var("SHOW TABLES LIKE '$wpws_scrapmeta'") != $wpws_scrapmeta) {
        $sql = "CREATE TABLE " . $wpws_scrapmeta . " (
			`meta_id` BIGINT(20) UNSIGNED NOT NULL auto_increment,
			`meta_key` VARCHAR(255) DEFAULT NULL,
			`meta_value` LONGTEXT,
			PRIMARY KEY (`meta_id`),
  			KEY `meta_key` (`meta_key`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
    }

    if ( !empty( $sql ) ) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

}

function wpws_admin_menu() {
    $wpws_options_page = add_options_page('WP Web Scraper Settings', 'WP Web Scraper', 8, 'wpws-options.php', 'wpws_options_page');
    add_action('admin_head-'.$wpws_options_page, 'wpws_options_admin_head');
}

function wpws_url_get_content($url = '', $postargs = '', $agent = 'WPWS bot', $timeout = 1) {
    if (empty($url))
        return array('key' => false, 'value' => 'WPWS Error: No URL specified');
    $curlopt = array(
            CURLOPT_RETURNTRANSFER	=> true,	// Return web page
            CURLOPT_HEADER		=> false,	// Don't return headers
            CURLOPT_FOLLOWLOCATION	=> true,	// Follow redirects
            CURLOPT_ENCODING		=> '',		// Handle all encodings
            CURLOPT_USERAGENT		=> $agent,	// Useragent
            CURLOPT_AUTOREFERER		=> true,	// Set referer on redirect
            CURLOPT_FAILONERROR		=> true,	// Fail silently on HTTP error
            CURLOPT_CONNECTTIMEOUT	=> $timeout,	// Timeout on connect
            CURLOPT_TIMEOUT		=> $timeout,	// Timeout on response
            CURLOPT_MAXREDIRS		=> 3,		// Stop after x redirects
            CURLOPT_SSL_VERIFYHOST	=> 0            // Don't verify ssl
    );
    $fopenopt = array(
            'user_agent'		=> $agent,	// Useragent
            'timeout'			=> $timeout,	// Timeout on connect
            'max_redirects' 		=> 3		// Stop after x redirects
    );
    if (!empty($postargs)) {
        $curlopt = array_merge($curlopt, array(CURLOPT_POST => true, CURLOPT_POSTFIELDS => $postargs));
        $fopenopt = array_merge($fopenopt, array('method'  => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $postargs));
    }
    $fopenopt = array('http' => $fopenopt);
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, $curlopt);
        $content = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if (empty($content))
            return array('key' => false, 'value' => 'WPWS Error: cURL error - '.$error);
        return array('key' => true, 'value' => $content);
    } elseif ((function_exists('file_get_contents')) && (strtolower(ini_get('allow_url_fopen')) == 'on' || ini_get('allow_url_fopen') == '1') || ini_get('allow_url_fopen') == true) {
        $context = stream_context_create($fopenopt);
        $content = @file_get_contents($url, false, $context);
        if ($content === false)
            return array('key' => false, 'value' => 'WPWS Error: Fopen error');
        return array('key' => true, 'value' => $content);
    } else {
        return array('key' => false, 'value' => 'WPWS Error: Failed to fetch external content using curl or fopen');
    }
}

function wpws_get_content($url = '', $selector = '', $wpwsopt = '') {
    global $wpdb;
    $defaults = array(
            'postargs' => '',
            'cache' => get_option('wpws_cache'),
            'user_agent' => get_option('wpws_user_agent'),
            'timeout' => get_option('wpws_timeout'),
            'on_error' => get_option('wpws_on_error'),
            'output' => 'html',
            'clear_regex' => '',
            'replace_regex' => '',
            'replace_with' => '',
            'basehref' => '',
            'striptags' => '',
            'debug' => '1',
            'htmldecode' => ''
    );
    $wpwsopt = wp_parse_args( $wpwsopt, $defaults );

    if($wpwsopt['debug'] == '1') {
        $header = "\n<!--\n Start of web scrap dump (created by wp-web-scraper)\n Source URL: $url \n CSS Selector: $selector \n-->\n";
        $footer = "\n<!--End of web scrap dump-->\n";
    } elseif ($wpwsopt['debug'] == '0') {
        $header = '';
        $footer = '';
    }

    define('wpws_HEADER', $header);
    define('wpws_FOOTER', $footer);

    if(empty($url) || empty($selector)) {
        if($wpwsopt['on_error'] == 'wpws_error_hide') {
            return wpws_HEADER.wpws_FOOTER;
        } else {
            return wpws_HEADER.'WPWS Error: No URL and/or selector specified'.wpws_FOOTER;
        }
    }
    
    if( strstr($url, '___QUERY_STRING___') ) {
        $url = str_replace('___QUERY_STRING___', $_SERVER['QUERY_STRING'], $url);
    } else {
        $url = preg_replace_callback('/___(.*?)___/', create_function('$matches','return $_REQUEST[$matches[1]];'), $url);
    }

    if( strstr($postargs, '___QUERY_STRING___') ) {
        $postargs = str_replace('___QUERY_STRING___', $_SERVER['QUERY_STRING'], $postargs);
    } else {
        $postargs = preg_replace_callback('/___(.*?)___/', create_function('$matches','return $_REQUEST[$matches[1]];'), $postargs);
    }

    $cache_key = $url;
    if (!empty($wpwsopt['postargs']))
        $cache_key .= '_postargs:'.$wpwsopt['postargs'];
    $cache_value = unserialize(wpws_get_meta($cache_key));
    if($cache_value) {
        $cache_status = (time() - $cache_value['timestamp']) < ($wpwsopt['cache'] * 60);
    } else {
        $cache_status = false;
    }
    if ($cache_status) {
        return wpws_parse_byselector($cache_value['data'], $url, $selector, $wpwsopt);
    } else {
        $scrap = @wpws_url_get_content(html_entity_decode($url), $wpwsopt['postargs'], $wpwsopt['user_agent'], $wpwsopt['timeout']);
        if ($scrap['key']) {
            wpws_update_meta($cache_key, serialize( array('timestamp' => time(), 'data' => $scrap['value']) ));
            return wpws_parse_byselector($scrap['value'], $url, $selector, $wpwsopt);
        } else {
            if ($wpwsopt['on_error'] == 'wpws_error_hide')
                return wpws_HEADER.wpws_FOOTER;
            if ($wpwsopt['on_error'] == 'wpws_error_show')
                return wpws_HEADER.$scrap['value'].wpws_FOOTER;
            if ($wpwsopt['on_error'] == 'wpws_error_show_cache') {
                if ($cache_value['data'])
                    return wpws_parse_byselector($cache_value['data'], $url, $selector, $wpwsopt);
                if (!$cache_value['data'])
                    return wpws_HEADER.$scrap['value'].wpws_FOOTER;
            }
        }
    }
}

function wpws_parse_byselector($scrap, $url, $selector, $wpwsopt) {
    global $wpdb;
    $currcharset = get_bloginfo('charset');
    require_once('includes/phpQuery-onefile.php');
    $doc = phpQuery::newDocumentHTML($scrap, $currcharset);
    phpQuery::selectDocument($doc);
    if($wpwsopt['output'] == 'text')
        $output = pq($selector)->text();
    if($wpwsopt['output'] == 'html')
        $output = pq($selector)->html();
    if(!empty($wpwsopt['clear_regex']))
        $output = preg_replace($wpwsopt['clear_regex'], '', $output);
    if(!empty($wpwsopt['replace_regex']))
        $output = preg_replace($wpwsopt['replace_regex'], $wpwsopt['replace_with'], $output);
    if(!empty($wpwsopt['basehref']))
        $output = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="'.$wpwsopt['basehref'].'$2$3',$output);
    if(!empty($wpwsopt['striptags']))
        $output = wpws_strip_only($output, $wpwsopt['striptags']);
    if(!empty($wpwsopt['htmldecode']))
        $output = iconv($wpwsopt['htmldecode'], $currcharset, $output);
    if(empty($output) && $wpwsopt['debug'] == 1) {
	$header = wpws_HEADER."<!--Warning: Your selector returned an empty string-->";
    } else {
        $header = wpws_HEADER;
    }
    return $header.trim($output).wpws_FOOTER;
}

function wpws_strip_only($str, $tags) {
    if(!is_array($tags)) {
        $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
        if(end($tags) == '') array_pop($tags);
    }
    foreach($tags as $tag) $str = preg_replace('#</?'.$tag.'[^>]*>#is', '', $str);
    return $str;
}

function wpws_shortcode($atts) {
    global $wpdb;
    extract( shortcode_atts( array(
            'url' => '',
            'postargs' => '',
            'selector' => '',
            'cache' => get_option('wpws_cache'),
            'user_agent' => get_option('wpws_user_agent'),
            'timeout' => get_option('wpws_timeout'),
            'on_error' => get_option('wpws_on_error'),
            'output' => 'html',
            'clear_regex' => '',
            'replace_regex' => '',
            'replace_with' => '',
            'basehref' => '',
            'striptags' => '',
            'debug' => '1',
            'htmldecode' => '',
            'urldecode' => '1'
            ), $atts));
    if($urldecode == '1') {
        $url = urldecode($url);
        $postargs = urldecode($postargs);
    }
    return wpws_get_content($url, $selector, 'postargs='.$postargs.'&cache='.$cache.'&user_agent='.$user_agent.'&timeout='.$timeout.'&on_error='.$on_error.'&output='.$output.'&clear_regex='.$clear_regex.'&replace_regex='.$replace_regex.'&replace_with='.$replace_with.'&basehref='.$basehref.'&striptags='.$striptags.'&debug='.$debug.'&htmldecode='.$htmldecode);
}

function wpws_debug() {
    $url_content = wpws_get_content('http://google.com','title','on_error=wpws_error_show&cache=1&timeout=2');
    if ( strpos($url_content,'WPWS Error: cURL error') !== false ) {
        return 'Fatel error: WP Web Scraper could not fetch content using cURL';
    } elseif ( strpos($url_content,'WPWS Error: Fopen error') !== false) {
        return 'Fatel error: WP Web Scraper could not fetch content using Fopen';
    } elseif ( strpos($url_content,'WPWS Error: Failed to fetch external content using curl or fopen') !== false) {
        return 'Fatel error: WP Web Scraper could not fetch content using cURL or Fopen';
    } else {
        return false;
    }
}

function wpws_get_meta($key, $single = true) {
    $key = stripslashes($key);
    global $wpdb;
    $table = $wpdb->prefix.'wpws_scrapmeta';
    $meta = $wpdb->get_results( "SELECT meta_value FROM $table WHERE meta_key = '$key'", ARRAY_A);
    if ( $single ) {
        return maybe_unserialize($meta[0]['meta_value']);
    } else {
        return array_map('maybe_unserialize', $meta);
    }
}

function wpws_add_meta($key, $value, $unique = true) {
    $key = stripslashes($key);
    global $wpdb;
    $table = $wpdb->prefix.'wpws_scrapmeta';
    if ( $unique && $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE meta_key = '$key'") ) {
        return false;
    } else {
        $value = maybe_serialize( stripslashes_deep($value) );
        $wpdb->insert($table, array(
                'meta_key' => $key,
                'meta_value' => $value
        ));
        return true;
    }
}

function wpws_update_meta($key, $value, $oldvalue = '') {
    $key = stripslashes($key);
    global $wpdb;
    $table = $wpdb->prefix.'wpws_scrapmeta';
    if ( !$wpdb->get_var("SELECT meta_id FROM $table WHERE meta_key = '$key'") )
        return wpws_add_meta($key, $value);
    $value = maybe_serialize( stripslashes_deep($value) );
    $where = array( 'meta_key' => $key );
    if ( !empty( $oldvalue ) ) {
        $oldvalue = maybe_serialize($oldvalue);
        $where['meta_value'] = $oldvalue;
    }
    $wpdb->update($table, array('meta_value' => $value), $where);
    return true;
}

?>