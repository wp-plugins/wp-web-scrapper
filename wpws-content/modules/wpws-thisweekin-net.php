<?php

/*
 * Unique identifier for wpws mod used for shortcode creation (wpws_$wpws_mod_name) 
 * and underlying function (wpws_shortcode_$wpws_mod_name). Mod file needs to have
 * at least have wpws_shortcode_$wpws_mod_name to function properly.
 */
$wpws_mod_name = 'thisweekin';

function wpws_shortcode_thisweekin($atts) {
    global $wpdb;
    $wpws_options = get_option('wpws_options');
    extract( shortcode_atts( array(
        'url' => '',
        'postargs' => '',
        'selector' => '',
        'xpath' => '',
        'cache' => $wpws_options['cache'],
        'user_agent' => $wpws_options['useragent'],
        'timeout' => $wpws_options['timeout'],
        'on_error' => $wpws_options['on_error'],
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
    return wpws_thisweekin_get_content($url, $selector, $xpath, 'postargs='.$postargs.'&cache='.$cache.'&user_agent='.$user_agent.'&timeout='.$timeout.'&on_error='.$on_error.'&output='.$output.'&clear_regex='.$clear_regex.'&replace_regex='.$replace_regex.'&replace_with='.$replace_with.'&basehref='.$basehref.'&striptags='.$striptags.'&debug='.$debug.'&htmldecode='.$htmldecode);
}

function wpws_thisweekin_get_content($url, $selector = '', $xpath = '', $wpwsopt = '') {
    $content = wpws_get_content($url, $selector, $xpath, $wpwsopt);
    $siteurl = get_option('wpurl');
    if( empty($siteurl) )
        $siteurl = get_option('siteurl');
    $cache_dir = ABSPATH.str_replace($siteurl.'/', '', get_bloginfo('template_directory')).'/wpws-cache/';
    if(!is_dir($cache_dir))
        mkdir($cache_dir, 0777);
    file_put_contents($cache_dir.sanitize_title( str_replace('http://', '', $url) ).'-wpws.php', $content);
    return $content;
}

?>