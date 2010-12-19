<?php

require_once 'wp-bootstrap.php';

/**
 * Load all module files in '/wp-web-scrapper/wpws-content/modules/
 */
global $wpdb;
$wpws_options = get_option('wpws_options');
foreach (glob(WP_PLUGIN_DIR.'/wp-web-scrapper/wpws-content/modules/*.php') as $mod) {
    require_once($mod);
    if(($wpws_options['sc_posts'] == 1) && (function_exists("wpws_shortcode_$wpws_mod_name")))
        add_shortcode("wpws_$wpws_mod_name", "wpws_shortcode_$wpws_mod_name");
}

if($wpws_options['sc_posts'] == 1)
    add_shortcode('wpws', 'wpws_shortcode');
if($wpws_options['sc_sidebar'] == 1)
    add_filter('widget_text', 'do_shortcode');

/**
 * Adds admin menu page(s)
 * @return null
 */
function wpws_admin_menu() {
    require_once WP_PLUGIN_DIR.'/wp-web-scrapper/wpws-admin/options.php';
    $wpws_options_page = add_options_page('WP Web Scraper Settings', 'WP Web Scraper','administrator', 'wpws-admin/options.php', 'wpws_options_page');
    add_action('admin_head-'.$wpws_options_page, 'wpws_options_admin_head');
}

/**
 * Adds wpws options
 * @return null
 */
function wpws_admin_init(){
    register_setting( 'wpws_options', 'wpws_options' );
}

/**
 * Adds the default options
 * @return null
 */
function wpws_register_activation_hook() {
    global $wpdb;
    $default_wpws_options = array(
        'sc_posts' => 1,
        'sc_widgets' => 1,
        'on_error' => 'error_hide',
        'useragent' => "WPWS bot (".get_bloginfo('url').")",
        'timeout' => 1,
        'cache' => 60
    );
    add_option('wpws_options', $default_wpws_options);
    add_shortcode('wpws', 'wpws_shortcode');
    add_filter('widget_text', 'do_shortcode');
}

/**
 * Shortcode wrapper
 */
function wpws_shortcode($atts) {
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
    $url = str_replace('&#038;', '&', $url);
    if($urldecode == '1') {
        $url = urldecode($url);
        $postargs = urldecode($postargs);
    }
    return wpws_get_content($url, $selector, $xpath, 'postargs='.$postargs.'&cache='.$cache.'&user_agent='.$user_agent.'&timeout='.$timeout.'&on_error='.$on_error.'&output='.$output.'&clear_regex='.$clear_regex.'&replace_regex='.$replace_regex.'&replace_with='.$replace_with.'&basehref='.$basehref.'&striptags='.$striptags.'&debug='.$debug.'&htmldecode='.$htmldecode);
}

/**
 * Wrapper function to fetch content, select / query it and parse it
 * @param string $url
 * @param string $selector (optional) Selector
 * @param string $xpath (optional) XPath
 * @param array $wpwsopt Options
 * @return string
 */
function wpws_get_content($url, $selector = '', $xpath = '', $wpwsopt = '') {
    global $wpdb;
    $wpws_options = get_option('wpws_options');
    $default_wpwsopt = array(
            'postargs' => '',
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
            'htmldecode' => ''
    );
    $wpwsopt = wp_parse_args( $wpwsopt, $default_wpwsopt );
    
    if($wpwsopt['debug'] == '1') {
        $header = "\n<!--\n Start of web scrap dump (created by wp-web-scraper)\n Source URL: $url \n CSS Selector: $selector \n-->\n";
        $footer = "\n<!--End of web scrap dump-->\n";
    } elseif ($wpwsopt['debug'] == '0') {
        $header = '';
        $footer = '';
    }

    if(empty($url)) {
        if($wpwsopt['on_error'] == 'error_hide') {
            return $header.$footer;
        } else {
            return "$header WPWS Error: No URL and/or selector specified.$footer";
        }
    }

    if( strstr($url, '___QUERY_STRING___') ) {
        $url = str_replace('___QUERY_STRING___', $_SERVER['QUERY_STRING'], $url);
    } else {
        $url = preg_replace_callback('/___(.*?)___/', create_function('$matches','return $_REQUEST[$matches[1]];'), $url);
    }

    if( strstr($wpwsopt['postargs'], '___QUERY_STRING___') ) {
        $wpwsopt['postargs'] = str_replace('___QUERY_STRING___', $_SERVER['QUERY_STRING'], $wpwsopt['postargs']);
    } else {
        $wpwsopt['postargs'] = preg_replace_callback('/___(.*?)___/', create_function('$matches','return $_REQUEST[$matches[1]];'), $wpwsopt['postargs']);
    }

    $cache_args['cache'] = $wpwsopt['cache'];
    if ( $wpwsopt['on_error'] == 'error_show_cache' )
        $cache_args['on-error'] = 'cache';

    if ( !empty($wpwsopt['postargs']) )
        $http_args['headers'] = $wpwsopt['postargs'];
    $http_args['useragent'] = $wpwsopt['user_agent'];
    $http_args['timeout'] = $wpwsopt['timeout'];

    $response = wpws_remote_request($url, $cache_args, $http_args);
    if( !is_wp_error( $response ) ) {
        $raw_html = $response['body'];
        if( !empty($selector) ) {
            $raw_html = wpws_get_html_by_selector($raw_html, $selector, $wpwsopt['output']);
             if( !is_wp_error( $raw_html ) ) {
                 $filtered_html = $raw_html;
             } else {
                 $err_str = $raw_html->get_error_message();
             }
        } elseif( !empty($xpath) ) {
            $raw_html = wpws_get_html_by_xpath($raw_html, $xpath, $wpwsopt['output']);
             if( !is_wp_error( $raw_html ) ) {
                 $filtered_html = $raw_html;
             } else {
                 $err_str = $raw_html->get_error_message();
             }
        } else {
            $filtered_html = $raw_html;
        }
        if( !empty($err_str) ) {
            if ($wpwsopt['on_error'] == 'error_hide')
                return $header.$footer;
            if ($wpwsopt['on_error'] == 'error_show')
                return $header.$err_str.$footer;
        }
        return $header.wpws_parse_filtered_html($filtered_html, $wpwsopt).$footer;
    } else {
        if ($wpwsopt['on_error'] == 'error_hide')
            return $header.$footer;
        if ($wpwsopt['on_error'] == 'error_show')
            return $header."Error fetching $url - ".$response->get_error_message().$footer;
    }
    
}

/**
 * Retrieve the raw response from the HTTP request (or its cached version).
 * Wrapper function to wp_remote_request()
 * @param string $url Site URL to retrieve.
 * @param array $cache_args Optional. Override the defaults.
 * @param array $http_args Optional. Override the defaults.
 * @return WP_Error|array The response or WP_Error on failure.
 */
function wpws_remote_request($url, $cache_args = array(), $http_args = array()) {
    $default_cache_args = array(
        'cache' => 60,
        'on-error' => 'cache'
    );
    $default_http_args = array(
        'user-agent' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)'
    );
    $cache_args = wp_parse_args( $cache_args, $default_cache_args );
    $http_args = wp_parse_args( $http_args, $default_http_args );
    if($cache_args['headers']) {
        $cache_file = WP_PLUGIN_DIR.'/wp-web-scrapper/wpws-content/http-cache/'.md5($url.serialize($cache_args['headers']));
    } else {
        $cache_file = WP_PLUGIN_DIR.'/wp-web-scrapper/wpws-content/http-cache/'.md5($url);
    }

    if( file_exists($cache_file) ) {
        $cache = unserialize( file_get_contents($cache_file) );
        $cache['headers']['source'] = 'Cache';
        $cache_status = ( time() - strtotime($cache['headers']['date']) ) < ($cache_args['cache'] * 60);
    } else {
        $cache_status = false;
    }

    if ($cache_status) {
        return $cache;
    } else {
        $response = wp_remote_request($url, $http_args);
        if( !is_wp_error( $response ) ) {
            $response['headers']['source'] = 'WP_Http';
            file_put_contents($cache_file, serialize( str_replace(array("\r","\n"), '', $response) ) );
            return $response;
        } else {
            if($cache_args['on-error'] == 'cache' && $cache)
                return $cache;
            return new WP_Error('wpws_remote_request_failed', $response->get_error_message());
        }
    }
}

/**
 * Get HTML from a web page using XPath query
 * @param string $raw_html Raw HTML
 * @param string $xpath XPath query
 * @param string $output html or text
 * @return string
 */
function wpws_get_html_by_xpath($raw_html, $xpath, $output = 'html'){
    // Parsing request using JS_Extractor
    require_once 'Extractor/Extractor.php';
    $extractor = new JS_Extractor($raw_html);
    $body = $extractor->query("body")->item(0);
    if (!$result = $body->query($xpath)->item(0)->nodeValue)
        return new WP_Error('wpws_get_html_by_xpath_failed', "Error parsing xpath: $xpath");
    if($output == 'text')
        return strip_tags($result);
    if($output == 'html')
        return $result;
}

/**
 * Get HTML from a web page using selector
 * @param string $raw_html Raw HTML
 * @param string $selector Selector
 * @param string $output html or text
 * @return string
 */
function wpws_get_html_by_selector($raw_html, $selector, $output = 'html'){
    // Parsing request using phpQuery
    global $wpdb;
    $currcharset = get_bloginfo('charset');
    require_once 'phpQuery-onefile.php';
    $phpquery = phpQuery::newDocumentHTML($raw_html, $currcharset);
    phpQuery::selectDocument($phpquery);
    if($output == 'text')
        return pq($selector)->text();
    if($output == 'html')
        return pq($selector)->html();
    if( empty($output) )
        return new WP_Error('wpws_get_html_by_selector_failed', "Error parsing selector: $selector");
}

/**
 * Parse filtered content using options
 * @param string $filtered_html Filtered HTML using selector or xpath query
 * @param array $wpwsopt Options array
 * @return string
 */
function wpws_parse_filtered_html($filtered_html, $wpwsopt) {
    global $wpdb;
    $currcharset = get_bloginfo('charset');
    if(!empty($wpwsopt['clear_regex']))
        $filtered_html = preg_replace($wpwsopt['clear_regex'], '', $filtered_html);
    if(!empty($wpwsopt['replace_regex']))
        $filtered_html = preg_replace($wpwsopt['replace_regex'], $wpwsopt['replace_with'], $filtered_html);
    if(!empty($wpwsopt['basehref']))
        $filtered_html = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="'.$wpwsopt['basehref'].'$2$3',$filtered_html);
    if(!empty($wpwsopt['striptags']))
        $filtered_html = wpws_strip_only($filtered_html, $wpwsopt['striptags']);
    if(!empty($wpwsopt['htmldecode']))
        $filtered_html = iconv($wpwsopt['htmldecode'], $currcharset, $filtered_html);
    return $filtered_html;
}

/**
 * Strip specified tags
 * @param string $str
 * @param string/array $tags
 * @return string
 */
function wpws_strip_only($str, $tags) {
    if(!is_array($tags)) {
        $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
        if(end($tags) == '') array_pop($tags);
    }
    foreach($tags as $tag) $str = preg_replace('#</?'.$tag.'[^>]*>#is', '', $str);
    return $str;
}

/**
 * Degug function
 * @return string
 */
function wpws_debug() {
    $url_content = wpws_get_content('http://google.com/','title','','on_error=error_show&cache=10&timeout=2');
    if ( strpos($url_content,'Error ') !== false ) {
        return 'Fatel error: WP Web Scraper could not fetch content - may not function properly';
    } else {
        return false;
    }
}

?>