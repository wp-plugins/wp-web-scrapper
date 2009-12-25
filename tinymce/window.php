<?php
// look up for the path
require_once( dirname( dirname(__FILE__) ) .'/wpws-bootstrap.php');

global $wpdb;
// check for rights
if ( !is_user_logged_in() || !current_user_can('edit_posts') ) 
	wp_die(__("You are not allowed to be here"));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Add a new web scrap</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript">
	function init() {
		tinyMCEPopup.resizeToInnerSize();
	}
	
	function insertwpws() {
		
		var tagtext;
		var other_params = '';
		var wpws_url = document.getElementById('wpws_url').value;
		var wpws_postargs = document.getElementById('wpws_postargs').value;
		var wpws_selector = document.getElementById('wpws_selector').value;
		var wpws_urldecode = document.getElementById('wpws_urldecode').value;
		var wpws_clear = document.getElementById('wpws_clear').value;
		var wpws_replace = document.getElementById('wpws_replace').value;
		var wpws_replace_text = document.getElementById('wpws_replace_text').value;
		var wpws_basehref = document.getElementById('wpws_basehref').value;
		var wpws_output = document.getElementById('wpws_output').value;
		var wpws_htmldecode = document.getElementById('wpws_htmldecode').value;
		var wpws_cache = document.getElementById('wpws_cache').value;
		var wpws_agent = document.getElementById('wpws_agent').value;
		var wpws_timeout = document.getElementById('wpws_timeout').value;
		var wpws_error = document.getElementById('wpws_error').value;
			
		if (wpws_url != '' || wpws_selector != '') {
			if(wpws_postargs != '') other_params += " postargs=\""+wpws_postargs+"\"";
			if(wpws_urldecode != '1') other_params += " urldecode=\""+wpws_urldecode+"\"";
			if(wpws_clear != '') other_params += " clear_regex=\""+wpws_clear+"\"";
			if(wpws_replace != '') other_params += " replace_regex=\""+wpws_replace+"\" replace_with=\""+wpws_replace_text+"\"";
			if(wpws_basehref != '') other_params += " basehref=\""+wpws_basehref+"\"";
			if(wpws_output != 'html') other_params += " output=\""+wpws_output+"\"";
			if(wpws_htmldecode != '') other_params += " htmldecode=\""+wpws_htmldecode+"\"";
			if(wpws_cache != '') other_params += " cache=\""+wpws_cache+"\"";
			if(wpws_agent != '') other_params += " useragent=\""+wpws_agent+"\"";
			if(wpws_timeout != '') other_params += " timeout=\""+wpws_timeout+"\"";
			if(wpws_error != '') other_params += " on_error=\""+wpws_error+"\"";
			tagtext = "[wpws url=\""+wpws_url+"\" selector=\""+wpws_selector+"\" "+other_params+"]";
			if(window.tinyMCE) {
				window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
				tinyMCEPopup.editor.execCommand('mceRepaint');
				tinyMCEPopup.close();
				return true;
			}				
		} else {
			alert('Source URL and CSS Selector are mandatory inputs to create your web scrap.');
			tinyMCEPopup.close();
		}
		
		return false;
	}
	
	function show_help(element) {
		var helptips = 
		{	'wpws_url': 'The complete URL which needs to be scraped including the protocol (http://)',
			'wpws_postargs': 'A string of post arguments to the page you are trying to scrap. For example id=197&cat=5',
			'wpws_selector': 'The (jQuery style) CSS selector string to select the content to be scraped. You can use elements, ids or classes for this. Further details about selector syntax in <a href="http://wordpress.org/extend/plugins/wp-web-scrapper/other_notes/" target="_blank">Selector Manual</a>',
			'wpws_urldecode': 'Set to yes to use `urldecode` for URLs with special characters.',
			'wpws_clear': 'Regex pattern to be cleared before the scraper flushes its output. The pattern string needs to be enclosed within /, for example /<img.*?>/ will remove all image tags from the output or /[copyright|domain]/ will remove the words copyright and domain from the output. This <a href="http://gnosis.cx/publish/programming/regular_expressions.html" target="_blank">Regex reference</a> will be helpful.',
			'wpws_replace': 'egex pattern to be replaced with some string before the scraper flushes its output. Similar to clear, this replaces the regex pattern with a string specified.',
			'wpws_replace_text': 'String which will replace the regex pattern specified.',
			'wpws_basehref': 'A parameter which can be used to convert relative links from the scrap to absolute links. This should be the complete URL which needs to be scraped including the protocol (http://). For example, if you are scraping Yahoo.com, using http://yahoo.com as basehref will convert all relative links to absolute by appending http://yahoo.com to all href and scr values. Note that basehref needs to be complete path without the trailing slash.',
			'wpws_output': 'Format of output rendered by the selector (text or html). Text format strips all HTML tags and returns only text content. HTML format retirns the scrap as is with the HTML tags.',
			'wpws_htmldecode': 'Specify a charset for `iconv` charset conversion of scraped content. You should specify the charset of the source url you are scraping from. If ignored, the default encoding of your blog will be used.',
			'wpws_cache': 'Timeout interval of the cached data in minutes. If ignored, the default value specified in plugin settings will be used.',
			'wpws_agent': 'The USERAGENT header for cURL. This string acts as your footprint while scraping data. If ignored, the default value specified in plugin settings will be used.',
			'wpws_timeout': 'Timeout interver for cURL function in seconds. Higer the better for scraping slow servers, but this will also increase your page load time. Ideally should not exceed 2. If ignored, the default value specified in plugin settings will be used.',
			'wpws_error': 'Prints an error if cURL fails and if this param is set as 1. If it is set as 0, it silently fails. If set to "cache" it will display data from expired cache (if any). Setting it to any other string will output the string itself. For instance screwed! will output screwed! if something goes wrong in the scrap. If ignored, the default value specified in plugin settings will be used.'
		};	
		document.getElementById('help').innerHTML = helptips[element];
	}
	
	</script>
	<base target="_self" />
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none">
<!-- <form onsubmit="insertLink();return false;" action="#"> -->
	<form name="cetsHelloWorld" action="#">
	<div class="tabs">
		<ul>
			<li id="required_tab" class="current"><span><a href="javascript:mcTabs.displayTab('required_tab','required_panel');" onmousedown="return false;">Main</a></span></li>
			<li id="output_tab"><span><a href="javascript:mcTabs.displayTab('output_tab','output_panel');" onmousedown="return false;">Output options</a></span></li>
			<li id="advanced_tab"><span><a href="javascript:mcTabs.displayTab('advanced_tab','advanced_panel');" onmousedown="return false;">Advanced options</a></span></li>
		</ul>
	</div>
	
	<div class="panel_wrapper">
		<div id="required_panel" class="panel current">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
			<tr>
				<td nowrap="nowrap"><label for="wpws_url">Source URL (Reg)</label></td>
				<td><input type="text" id="wpws_url" name="wpws_url" style="width: 190px" onfocus="show_help(this.id)"/></td>
			</tr>			
			<tr>
				<td nowrap="nowrap"><label for="wpws_selector">CSS Selector (Req)</label></td>
				<td><input type="text" id="wpws_selector" name="wpws_selector" style="width: 190px" onfocus="show_help(this.id)"/></td>
			</tr> 
			<tr>
				<td nowrap="nowrap"><label for="wpws_postargs">Post Args</label></td>
				<td><input type="text" id="wpws_postargs" name="wpws_postargs" style="width: 190px" onfocus="show_help(this.id)"/></td>
			</tr>			
			<tr>
				<td nowrap="nowrap"><label for="wpws_urldecode">URL Decode</label></td>
				<td>
				<select id="wpws_urldecode" name="wpws_urldecode" style="width: 190px" onfocus="show_help(this.id)">
					<option value="1" selected="selected">Yes</option>
					<option value="0">No</option>
				</select>
				</td>
			</tr>																	   
        </table>
		</div>
		<div id="output_panel" class="panel">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">     
			<tr>
				<td nowrap="nowrap"><label for="wpws_clear">Clear (Regex)</label></td>
				<td><input type="text" id="wpws_clear" name="wpws_clear" style="width: 190px" onfocus="show_help(this.id)"/></td>
			</tr> 
			<tr>
				<td nowrap="nowrap"><label for="wpws_replace">Replace (Regex)</label></td>
				<td><input type="text" id="wpws_replace" name="wpws_replace" style="width: 80px" onfocus="show_help(this.id)"/> <label for="wpws_replace_text"><?php _e("with"); ?></label> <input type="text" id="wpws_replace_text" name="wpws_replace_text" style="width: 80px" onfocus="show_help(this.id)"/></td>
			</tr> 
			<tr>
				<td nowrap="nowrap"><label for="wpws_basehref">Basehref</label></td>
				<td><input type="text" id="wpws_basehref" name="wpws_basehref" style="width: 190px" onfocus="show_help(this.id)"/></td>
			</tr> 
			<tr>
				<td nowrap="nowrap"><label for="wpws_output">Output format</label></td>
				<td>
				<select id="wpws_output" name="wpws_output" style="width: 190px" onfocus="show_help(this.id)">
					<option value="html" selected="selected">HTML</option>
					<option value="text">Text</option>
				</select>
				</td>
			</tr>
			<tr>
				<td nowrap="nowrap"><label for="wpws_htmldecode">Html Decode</label></td>
				<td><input type="text" id="wpws_htmldecode" name="wpws_htmldecode" style="width: 190px" onfocus="show_help(this.id)"/></td>
			</tr>																		   
        </table>		
		</div>
		<div id="advanced_panel" class="panel">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">     
			<tr>
				<td nowrap="nowrap"><label for="wpws_cache">Cache (mins)</label></td>
				<td><input type="text" id="wpws_cache" name="wpws_cache" style="width: 190px" onfocus="show_help(this.id)"/></td>
			</tr> 
			<tr>
				<td nowrap="nowrap"><label for="wpws_agent">Useragent</label></td>
				<td><input type="text" id="wpws_agent" name="wpws_agent" style="width: 190px" onfocus="show_help(this.id)"/></td>
			</tr> 
			<tr>
				<td nowrap="nowrap"><label for="wpws_timeout">Timeout</label></td>
				<td><input type="text" id="wpws_timeout" name="wpws_timeout" style="width: 190px" onfocus="show_help(this.id)"/></td>
			</tr> 
			<tr>
				<td nowrap="nowrap"><label for="wpws_error">Error reporting</label></td>
				<td><input type="text" id="wpws_error" name="wpws_error" style="width: 190px" onfocus="show_help(this.id)"/></td>
			</tr> 																   
        </table>		
		</div>		
		
	</div>

	<div id="help" style="height:80px; margin-top:5px; background-color:#ffffff; border:#999999 1px solid; padding:3px; font-size:10px; line-height:14px; overflow:auto; color:#333333">
	Click on any field for tips
	</div>	

	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel"); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert"); ?>" onclick="insertwpws();" />
		</div>

	</div>
	
</form>

</body>
</html>
<?php

?>
