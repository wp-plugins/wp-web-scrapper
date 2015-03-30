<?php

/*
Plugin Name: WP Web Scraper
Plugin URI: http://wp-ws.net/
Description: An easy to implement web scraper for WordPress. Display realtime data from any website directly into your posts, pages or sidebar.
Version: 3.5
Author: Akshay Raje
Author URI: http://webdlabs.com/
*/

// Make sure we don't expose any info if called directly. Silence is golden.
if (!function_exists('add_action'))
	exit;

define('WPWS__PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPWS__PLUGIN_FILE', plugin_basename(__FILE__));
define('WPWS__VERSION', '3.5');

require_once( WPWS__PLUGIN_DIR . 'class.wpws.php' );

register_activation_hook(__FILE__, array('WP_Web_Scraper', 'plugin_activate'));
register_deactivation_hook(__FILE__, array('WP_Web_Scraper', 'plugin_deactivate'));

add_action('init', array('WP_Web_Scraper', 'init'));

if (is_admin()) {
	require_once( WPWS__PLUGIN_DIR . 'class.wpws-admin.php' );
	add_action('init', array('WP_Web_Scraper_Admin', 'init'));
}

$wpws_options = get_option('wpws_options');
if ( !function_exists('wpws_get_content') && $wpws_options['tt'] == 1 ) {
    function wpws_get_content($url, $query = '', $args = array()){
        return WP_Web_Scraper::get_content($url, $query, $args);
    }
}