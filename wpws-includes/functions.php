<?php

function wpws_init(){
    $wpws_options = get_option('wpws_options');
    if($wpws_options['sc_posts'] == 1)
        add_shortcode('wpws', 'wpws_shortcode');
    if($wpws_options['sc_widgets'] == 1)
        add_filter('widget_text', 'do_shortcode');
}

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
    $default_wpws_options = array(
        'sc_posts' => 1,
        'sc_widgets' => 1,
        'on_error' => 'error_hide',
        'custom_error' => 'Unable to fetch data',
        'useragent' => "WPWS bot (".get_bloginfo('url').")",
        'timeout' => 2,
        'cache' => 60
    );
    add_option('wpws_options', $default_wpws_options);
    add_shortcode('wpws', 'wpws_shortcode');
    add_filter('widget_text', 'do_shortcode');
    // Define and create required directories
    $required_dir = array(
        'modules' => WP_PLUGIN_DIR.'/wp-web-scrapper/wpws-content/modules',
        'http-cache' => WP_PLUGIN_DIR.'/wp-web-scrapper/wpws-content/http-cache'
    );
    foreach ($required_dir as $dir)
        if( !is_dir($dir) ) @mkdir($dir, 0777);
}

/**
 * Shortcode wrapper
 */
function wpws_shortcode($atts) {
    $wpws_options = get_option('wpws_options');
    $default_wpwsopt = array(
        'url' => '',
        'urldecode' => '1',
        'xpathdecode' => '',
        'request_mt' => microtime(true)
    );
    $atts['url'] = str_replace(array('&#038;','&#38;','&amp;'), '&', $atts['url']);
    if($atts['urldecode'] == '1') {
        $atts['url'] = urldecode($atts['url']);
        $atts['postargs'] = urldecode($atts['postargs']);
    }
    if($atts['xpathdecode'] == '1')
        $atts['xpath'] = urldecode($atts['xpath']);
    $wpwsopt = wp_parse_args( $atts, $default_wpwsopt );
    return wpws_get_content($atts['url'], $atts['selector'], $atts['xpath'], $wpwsopt);
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
    $wpws_options = get_option('wpws_options');
    $default_wpwsopt = array(
            'postargs' => '',
            'cache' => $wpws_options['cache'],
            'user_agent' => $wpws_options['useragent'],
            'timeout' => $wpws_options['timeout'],
            'on_error' => $wpws_options['on_error'],
            'output' => 'html',
            'clear_regex' => '',
            'clear_selector' => '',
            'replace_regex' => '',
            'replace_selector' => '',
            'replace_with' => '',
            'replace_selector_with' => '',
            'basehref' => '',
            'striptags' => '',
            'removetags' => '',
            'callback' => '',
            'debug' => '1',
            'htmldecode' => ''
    );
    $wpwsopt = wp_parse_args( $wpwsopt, $default_wpwsopt );
    unset($wpwsopt['url']);
    unset($wpwsopt['selector']);
    unset($wpwsopt['xpath']);

    if($wpwsopt['debug'] == '1') {
        $header = "\n<!--\n Start of web scrap (created by wp-web-scraper)\n Source URL: $url \n Selector: $selector\n Xpath: $xpath";
        $footer = "\n<!--\n End of web scrap";
    } elseif ($wpwsopt['debug'] == '0') {
        $header = '';
        $footer = '';
    }

    if(empty($url)) {
        $header .= "\n Other options: ".print_r($wpwsopt, true)."-->\n";
        if($wpwsopt['on_error'] == 'error_hide') {
            return $header.$footer;
        } else {
            return "$header WPWS Error: No URL and/or selector specified $footer";
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

    if ( !empty($wpwsopt['postargs']) ) {
        $http_args['headers'] = $wpwsopt['postargs'];
        $cache_args['headers'] = $wpwsopt['postargs'];
    }
    $http_args['user-agent'] = $wpwsopt['user_agent'];
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
            if($wpwsopt['debug'] == '1') {
                $header .= "\n Other options: ".print_r($wpwsopt, true)."-->\n";
                $footer .= "\n Computing time: ".round(microtime(true) - $wpwsopt['request_mt'], 4)." seconds \n-->\n";
            }
            if ($wpwsopt['on_error'] == 'error_hide')
                return $header.$footer;
            if ($wpwsopt['on_error'] == 'error_show')
                return $header.$err_str.$footer;
            if ( !empty($wpwsopt['on_error']) )
                return $header.$wpwsopt['on_error'].$footer;
        }
        if($wpwsopt['debug'] == '1') {
            $header .= "\n Delivered thru: ".$response['headers']['source']."\n WPWS options: ".print_r($wpwsopt, true)."-->\n";
            //$header .= "\n WPWS options: ".print_r($wpwsopt, true)."-->\n";
            $footer .= "\n Computing time: ".round(microtime(true) - $wpwsopt['request_mt'], 4)." seconds \n-->\n";
        }
        return $header.wpws_parse_filtered_html($filtered_html, $wpwsopt).$footer;
    } else {
        if($wpwsopt['debug'] == '1') {
            $header .= "\n Other options: ".print_r($wpwsopt, true)."-->\n";
            $footer .= "\n Computing time: ".round(microtime(true) - $wpwsopt['request_mt'], 4)." seconds \n-->\n";
        }
        if ($wpwsopt['on_error'] == 'error_hide')
            return $header.$footer;
        if ($wpwsopt['on_error'] == 'error_show')
            return $header."Error fetching $url - ".$response->get_error_message().$footer;
        if ( !empty($wpwsopt['on_error']) )
            return $header.$wpwsopt['on_error'].$footer;
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
        $transient = md5($url.serialize($cache_args['headers']));
    } else {
        $transient = md5($url);
    }

    if ( false === ( $cache = get_transient($transient) ) || $cache_args['cache'] == 0 ) {
         $response = wp_remote_request($url, $http_args);
        if( !is_wp_error( $response ) ) {
            if($cache_args['cache'] != 0)
                set_transient($transient, $response, $cache_args['cache'] * 60 );
            @$response['headers']['source'] = 'WP_Http';
            return $response;
        } else {
            return new WP_Error('wpws_remote_request_failed', $response->get_error_message());
        }
    } else {
        $cache = get_transient($transient);
        @$cache['headers']['source'] = 'Cache';
        return $cache;
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
    $currcharset = get_bloginfo('charset');
    if(!empty($wpwsopt['clear_regex']))
        $filtered_html = preg_replace($wpwsopt['clear_regex'], '', $filtered_html);
    if(!empty($wpwsopt['clear_selector']))
        $filtered_html = str_replace(wpws_get_html_by_selector($filtered_html, $wpwsopt['clear_selector']), '', $filtered_html);
    if(!empty($wpwsopt['replace_regex']))
        $filtered_html = preg_replace($wpwsopt['replace_regex'], $wpwsopt['replace_with'], $filtered_html);
    if(!empty($wpwsopt['replace_selector']))
        $filtered_html = str_replace(wpws_get_html_by_selector($filtered_html, $wpwsopt['replace_selector']), $wpwsopt['replace_selector_with'], $filtered_html);
    if(!empty($wpwsopt['basehref']))
        $filtered_html = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="'.$wpwsopt['basehref'].'$2$3',$filtered_html);
    if(!empty($wpwsopt['striptags']))
        $filtered_html = wpws_strip_only($filtered_html, $wpwsopt['striptags']);
    if(!empty($wpwsopt['removetags']))
        $filtered_html = wpws_strip_only($filtered_html, $wpwsopt['removetags'], true);
    if(!empty($wpwsopt['htmldecode']))
        $filtered_html = iconv($wpwsopt['htmldecode'], $currcharset, $filtered_html);
    if(!empty($wpwsopt['callback']) && function_exists($wpwsopt['callback']))
        $filtered_html = call_user_func($wpwsopt['callback'], $filtered_html);
    return $filtered_html;
}

/**
 * Strip specified tags
 * @param string $str
 * @param string/array $tags
 * @param bool $strip_content
 * @return string
 */
function wpws_strip_only($str, $tags, $strip_content = false) {
    $content = '';
    if(!is_array($tags)) {
        $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
        if(end($tags) == '') array_pop($tags);
    }
    foreach($tags as $tag) {
        if ($strip_content)
             $content = '(.+</'.$tag.'(>|\s[^>]*>)|)';
         $str = preg_replace('#</?'.$tag.'(>|\s[^>]*>)'.$content.'#is', '', $str);
    }
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