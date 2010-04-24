<?php function wpws_options_admin_head(){ ?>
<style type="text/css">
.container {width: 100%; margin: 10px 0px; font-family: "Lucida Grande", Verdana, Arial, "Bitstream Vera Sans", sans-serif;}
ul.tabs {margin: 0;padding: 0;float: left;list-style: none;height: 25px;border-bottom: 1px solid #e3e3e3;border-left: 1px solid #e3e3e3;width: 100%;}
ul.tabs li {float: left;margin: 0;padding: 0;	height: 24px;line-height: 24px;border: 1px solid #e3e3e3;border-left: none;margin-bottom: -1px;background:#EBEBEB;overflow: hidden;position: relative; background-repeat:repeat-x;}
ul.tabs li a {text-decoration: none;color: #21759b;display: block;font-size: 12px;padding: 0 20px;border: 1px solid #fff;outline: none;}
ul.tabs li a:hover {color: #d54e21;}	
html ul.tabs li.active, html ul.tabs li.active a:hover  {background: #fff;border-bottom: 1px solid #fff;}
.tab_container {border: 1px solid #e3e3e3;border-top: none;clear: both;float: left; width: 100%;background: #fff;font-size:11px;}
.tab_content {padding: 20px;font-size: 1.2em;}
.tab_content h3 {margin-top:0px;margin-bottom:10px;}
.tab_content .head-description{font-style:italic;}
.tab_content .description{padding-left:15px}
.tab_content ul li{list-style:square outside; margin-left:20px}
</style>
<script type="text/javascript">
jQuery(document).ready(function() {
	//Default Action
	jQuery(".tab_content").hide(); //Hide all content
	jQuery("ul.tabs li:first").addClass("active").show(); //Activate first tab
	jQuery(".tab_content:first").show(); //Show first tab content
	//On Click Event
	jQuery("ul.tabs li").click(function() {
		jQuery("ul.tabs li").removeClass("active"); //Remove any "active" class
		jQuery(this).addClass("active"); //Add "active" class to selected tab
		jQuery(".tab_content").hide(); //Hide all tab content
		var activeTab = jQuery(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content
		jQuery(activeTab).show(); 
		return false;
	});
});
</script>
<?php
} /* EO function wpws_options_admin_head() */

function wpws_options_page(){
	$wpws_debug = wpws_debug(); 
	if ($wpws_debug !== false) { ?> 
<div id="message" class="error"><p><strong><?php echo $wpws_debug ?></strong></p></div>
<?php } ?>
<div class="wrap">

<?php if(function_exists(screen_icon)) {screen_icon();} ?>
<h2><?php _e('WP Web Scraper Settings'); ?></h2>
<small>Powered by <a href="http://webdlabs.com" target="_blank">webdlabs.com</a>. Please <a href="http://webdlabs.com/projects/donate/" target="_blank">donate (by paypal)</a> if you found this useful.</small>

<div class="container">
	<ul class="tabs">
		<li><a href="#tab1">Options</a></li>
		<li><a href="#tab2">FAQs</a></li>
		<li><a href="#tab3">Usage Manual</a></li>
		<li><a href="#tab4">About</a></li>
	</ul>
	<div class="tab_container">
		<div id="tab1" class="tab_content">
			<form method="post" action="options.php" id="wpws_options">
			<?php wp_nonce_field('update-options'); ?>
				<table class="form-table">
				<tr valign="top">
					<th scope="row"><label>WP Web Scraper Shortcodes</label></th>
					<td><fieldset>
					<label for="wpws_sc_posts">
					<input name="wpws_sc_posts" type="checkbox" id="wpws_sc_posts" value="1" <?php checked('1', get_option('wpws_sc_posts')); ?> />
					Enable shortcodes for posts and pages</label>
					<br />
					<label for="wpws_sc_sidebar">
					<input name="wpws_sc_sidebar" type="checkbox" id="wpws_sc_sidebar" value="1" <?php checked('1', get_option('wpws_sc_sidebar')); ?> />
					Enable shortcodes in sidebar text widget</label>
					</fieldset></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label>Error handlng options</label></th>
					<td>
					<select name="wpws_curl_error" id="wpws_curl_error" style="width:325px" class="regular-text code" >
						<option value="wpws_error_hide"<?php selected('wpws_error_hide', get_option('wpws_error_hide')); ?>>Fail silently (diplays blank string on failure)</option>
						<option value="wpws_error_show"<?php selected('wpws_error_show', get_option('wpws_error_show')); ?>>Display error (can be used while debuging)</option>
						<option value="wpws_error_show_cache"<?php selected('wpws_error_show_cache', get_option('wpws_error_show_cache')); ?>>Force display cache (even if expired)</option>
					</select>
					<span class="setting-description">Default error handling. Fail silently, display error or display expired cache.</span>		</td>
				</tr>		
				<tr valign="top">
					<th scope="row"><label>Useragent string</label></th>
					<td>
					<input name="wpws_user_agent" type="text" id="wpws_user_agent" value="<?php form_option('wpws_user_agent'); ?>" class="regular-text code" />
					<span class="setting-description">Default useragent header to identify yourself when crawling sites.</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label> Timeout (in seconds)</label></th>
					<td>
					<input name="wpws_timeout" type="text" id="wpws_timeout" value="<?php form_option('wpws_timeout'); ?>" class="small-text code" />
					<span class="setting-description">Default timeout interval in seconds for cURL or Fopen. Larger interval might slow down your page.</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label>Cache expiry (in minutes)</label></th>
					<td>
					<input name="wpws_cache" type="text" id="wpws_cache" value="<?php form_option('wpws_cache'); ?>" class="small-text code"/>
					<span class="setting-description">Default cache expiry in minutes for cached webpages.</span>
					</td>
				</tr>
				</table>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="wpws_sc_posts,wpws_sc_sidebar,wpws_curl_error,wpws_user_agent,wpws_timeout,wpws_cache" />
			<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
			</form>
		</div>
		<div id="tab2" class="tab_content">
		<?php echo wpws_get_content('http://wordpress.org/extend/plugins/wp-web-scrapper/faq/?v20','div.block-content:eq(0)','on_error=wpws_error_show_cache&timeout=3&cache=720000') ?>
		</div>
		<div id="tab3" class="tab_content">
		<?php echo wpws_get_content('http://wordpress.org/extend/plugins/wp-web-scrapper/other_notes/?v20','div.block-content:eq(0)','on_error=wpws_error_show_cache&timeout=3&cache=720000') ?>		
		</div>
		<div id="tab4" class="tab_content">
		<h4>About WP Web Scraper</h4>
		<p>An easy to implement professional web scraper for WordPress. This can be used to display realtime data from any websites directly into your posts, pages or sidebar. Use this to include realtime stock quotes, cricket or soccer scores or any other generic content. The scraper is built using timetested libraries cURL for scraping and phpQuery for parsing HTML.</p>
		<p>For a custom mod or assistance in creation of advanced shortcodes please write to me at akshay.raje@gmail.com</p>
		<h4>Supported by donations from</h4>
		Jets (info@internetwerkt.nl), Dino (dinor@equotedata.com), Dimitriy (vakhman@sbcglobal.net), Jeremy (crasymaker@yahoo.com), Andy (andy@uk-solutions.co.uk), Daisuke (bluesful@yahoo.co.jp), Donny Cruce (dyanni@windstream.net), Erik Hansén (erik.hansen@live.se), Daniel O'Prey (admin@pixelcityhome.com), Gregory Reddin (greddin@yahoo.com) and Bob Hendren (bhendren@listingware.com)
		</div>			
	</div>
</div>
<?php } /* EO function wpws_options_page() */ ?>
