<?php
/*
Plugin Name: WP Web Scrapper
Plugin URI: http://webdlabs.com/projects/wp-web-scraper/
Description: An easy to implement web scraper for WordPress. Display realtime data from any websites directly into your posts, pages or sidebar.
Author: Akshay Raje
Version: 2.2
Author URI: http://webdlabs.com
*/

require_once('wpws-includes.php');
require_once('wpws-html.php');

/* Use the register_activation_hook to set default values */
register_activation_hook(__FILE__, 'wpws_register_activation_hook');

/* Use the admin_menu action to add options page */
add_action('admin_menu', 'wpws_admin_menu');

?>