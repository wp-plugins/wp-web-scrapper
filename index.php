<?php
/*
Plugin Name: WP Web Scrapper
Plugin URI: http://webdlabs.com/projects/wp-web-scraper/
Description: An easy to implement web scraper for WordPress. Display realtime data from any websites directly into your posts, pages or sidebar.
Author: Akshay Raje
Version: 2.8
Author URI: http://webdlabs.com
*/

require_once('wpws-includes/functions.php');

// Use the register_activation_hook to set default values
register_activation_hook(__FILE__, 'wpws_register_activation_hook');

// Use the init action
add_action('init', 'wpws_init' );

// Use the admin_menu action to add options page
add_action('admin_menu', 'wpws_admin_menu');

// Use the admin_init action to add register_setting
add_action('admin_init', 'wpws_admin_init' );

?>