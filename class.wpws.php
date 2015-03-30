<?php

class WP_Web_Scraper {
	
	public static $url;
	public static $query;
	public static $args;
	public static $xcache;
	public static $error;
	public static $microtime;

	public static function init() {
		$wpws_options = get_option('wpws_options');
		if($wpws_options['sc_posts'] == 1)
			add_shortcode('wpws', array( 'WP_Web_Scraper', 'shortcode' ));
		if($wpws_options['sc_widgets'] == 1)
			add_filter('widget_text', 'do_shortcode');		
	}
	
	public static function view( $name, $vars = array() ) {
		load_plugin_textdomain( 'wp-web-scraper' );
		$file = WPWS__PLUGIN_DIR . 'views/'. $name . '.php';
		if(!empty($vars))
			extract( $vars, EXTR_OVERWRITE );
        unset( $vars );
		include( $file );
	}	

	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activate() {
		$default_wpws_options = array(
			'sc_posts' => 1,
			'sc_widgets' => 1,
			'tt' => 1,
			'on_error' => 'error_show',
			'useragent' => 'WPWS bot ('.get_bloginfo('url').')',
			'timeout' => 2,
			'cache' => 60
		);
		add_option( 'wpws_options', $default_wpws_options );		
	}

	/**
	 * Removes all connection options
	 * @static
	 */
	public static function plugin_deactivate() {
		
	}
	
	public static function shortcode($atts){
		$default_args = array(
			'url' => '',
			'query' => '',
			'urldecode' => 1,
			'querydecode' => 0
		);
        // For backward compatibility:
        if( isset($atts['selector']) ){
            $atts['query'] = $atts['selector'];
            $atts['query_type'] = 'cssselector';
        }
        if( isset($atts['xpath']) ){
            $atts['query'] = $atts['xpath'];
            $atts['query_type'] = 'xpath';
        }  
        $args = wp_parse_args( $atts, $default_args );
		$args['url'] = str_replace(array('&#038;','&#38;','&amp;'), '&', $args['url']);
		$args['headers'] = str_replace(array('&#038;','&#38;','&amp;'), '&', $args['headers']);
		if($args['urldecode'] == 1) {
			$args['url'] = urldecode($args['url']);
            if( isset($args['headers']) )
            	$args['headers'] = urldecode($args['headers']);
		}
		if($args['querydecode'] == 1){
			$args['query'] = urldecode($args['query']);
            if( isset($args['remove_query']) )
                $args['remove_query'] = urldecode($args['remove_query']);
            if( isset($args['replace_query']) )
                $args['replace_query'] = urldecode($args['replace_query']);            
        }
		return WP_Web_Scraper::get_content($args['url'], $args['query'], $args);
	}
	
	/**
	 * Core method to fetch content and parse it based on query
	 * @param string $url
	 * @param string $query (optional) CSS Selector or XPath query
	 * @param array $args (optional) Options
	 * @return string
	 */	
	public static function get_content($url, $query = '', $args = array()) {
		
        $mt_start = microtime(true);
        
		require_once 'class.wpws-parser.php';
		
		// Resolving ___QUERY_STRING___ and ___(.*?)___ in $url and $args['headers']
		if( strpos($url, '__') !== false)
			if( strpos($url, '___QUERY_STRING___') !== false )
				$url = str_replace('___QUERY_STRING___', $_SERVER['QUERY_STRING'], $url);
			else
				$url = preg_replace_callback('/___(.*?)___/', create_function('$matches','return $_REQUEST[$matches[1]];'), $url);

		if( isset($args['headers']) && strpos($args['headers'], '__') !== false)
			if( strstr($args['headers'], '___QUERY_STRING___') )
				$args['headers'] = str_replace('___QUERY_STRING___', $_SERVER['QUERY_STRING'], $args['headers']);
			else
				$args['headers'] = preg_replace_callback('/___(.*?)___/', create_function('$matches','return $_REQUEST[$matches[1]];'), $args['headers']);
		
		$default_args = WP_Web_Scraper::get_default_args();
        
		WP_Web_Scraper::$url = $url;
		WP_Web_Scraper::$query = $query;
		WP_Web_Scraper::$args = wp_parse_args( $args, $default_args );
		WP_Web_Scraper::$error = null;
		
		$response = WP_Web_Scraper::remote_request($url, WP_Web_Scraper::$args);
		
		if( !is_wp_error( $response ) ) {
			
			$wpws_parser = new WP_Web_Scraper_Parser( $response['body'], WP_Web_Scraper::$args['charset'] );
			WP_Web_Scraper::$xcache = $response['headers']['X-WPWS-Cache-Control'];
			
			if($query === ''){
				
				$content = $response['body'];
				
			} else {
				
				if(WP_Web_Scraper::$args['query_type'] === 'cssselector')
					$wpws_parser->parse_selector( $query );
				if(WP_Web_Scraper::$args['query_type'] === 'xpath')
					$wpws_parser->parse_xpath( $query );
				if(WP_Web_Scraper::$args['query_type'] === 'regex')
					$wpws_parser->parse_regex( $query );                
				
				if($wpws_parser->error !== null){
					WP_Web_Scraper::$error = "Error parsing: ".$wpws_parser->error;
				} elseif(version_compare(PHP_VERSION, '5.3.3', '<')){
                    WP_Web_Scraper::$error = "Error parsing: PHP version 5.3.3 or greater is required for parsing";
                } else {
					$content = $wpws_parser->result;
				}	
			}
			
		} else {
			
			WP_Web_Scraper::$error = "Error fetching: ".$response->get_error_message();
		}
        
        
        $ob_header = PHP_EOL;
        $ob_footer = PHP_EOL;
        
        if ( WP_Web_Scraper::$args['debug'] == 1 ){
            $ob_header = PHP_EOL.
                    '<!--' . PHP_EOL .
                    ' Start of web scrap (created by wp-web-scraper)' . PHP_EOL .
                    ' Source URL: ' . WP_Web_Scraper::$url . PHP_EOL .
                    ' Query: '. WP_Web_Scraper::$query . ' (' . WP_Web_Scraper::$args['query_type'] . ')' . PHP_EOL .
                    ' Other options: ' . print_r(WP_Web_Scraper::$args, true) . '-->' . PHP_EOL;
            $ob_footer =  PHP_EOL.
                    '<!--' . PHP_EOL .
                    ' End of web scrap' . PHP_EOL .
                    ' WPWS Cache Control: ' . WP_Web_Scraper::$xcache . PHP_EOL .
                    ' Computing time: ' . round(microtime(true) - $mt_start, 4) . ' seconds' . PHP_EOL .
                    '-->' . PHP_EOL;
        }        
		
		if (WP_Web_Scraper::$error === null) {

			$ob_body = WP_Web_Scraper::filter_content($content, WP_Web_Scraper::$args);
			
		} else {
			
			if ( WP_Web_Scraper::$args['on_error'] === 'error_hide' ){
				$ob_body = '';
            } elseif ( WP_Web_Scraper::$args['on_error'] === 'error_show' ){
				$ob_body = WP_Web_Scraper::$error;
            } elseif ( !empty( WP_Web_Scraper::$args['on_error'] ) ){
				$ob_body = WP_Web_Scraper::$args['on_error'];	
            }
			
		}
        
        return $ob_header.$ob_body.$ob_footer;
		
	}
	
	public static function filter_content($content, $response_args = array()){
        
        // Callback (Raw)
        if( $response_args['callback_raw'] !== '' && is_callable( $response_args['callback_raw'] ) === true )
            $content = call_user_func( $response_args['callback_raw'], $content);
        
        // Filtering if $content is an array (via parser)
        if( is_array($content) ){
		
            $i = array();

            // Get $i based on eq
            if( $response_args['eq'] !== '' ){
                if( strtolower( $response_args['eq'] ) === 'last' ) 
                    $i[] = count( $content ) - 1;
                if( strtolower( $response_args['eq'] ) === 'first' ) 
                    $i[] = 0;
                if( is_numeric( $response_args['eq'] ) === true )
                    $i[] = round( $response_args['eq'] );
            }

            // Get $i based on gt & lt both
            if( ($response_args['gt'] !== '' && is_numeric( $response_args['gt'] )) && ($response_args['lt'] !== '' && is_numeric( $response_args['lt'] )) && (round( $response_args['gt'] ) < round( $response_args['lt'] )) )
                for ($j = round( $response_args['gt'] ) + 1; $j <= round( $response_args['lt'] ) - 1; $j++)
                    $i[] = $j;            

            // Get $i based on gt only
            if( ($response_args['gt'] !== '' && is_numeric( $response_args['gt'] )) && ($response_args['lt'] == '' || !is_numeric( $response_args['lt'] )) )
                for ($j = round( $response_args['gt'] ) + 1; $j <= count($content); $j++)
                    $i[] = $j;

            // Get $i based on lt only
            if( ($response_args['lt'] !== '' && is_numeric( $response_args['lt'] )) && ($response_args['gt'] == '' || !is_numeric( $response_args['gt'] )) )
                for ($j = min( round( $response_args['lt'] ) - 1, count($content) ); $j >= 0; $j--)
                    $i[] = $j;                

            // Filter based on eq, gt or lt
            if(!empty($i)){
                foreach($content as $key => $value)
                    if(in_array($key, $i))
                        $filtered_content[] = $value;
                $content = $filtered_content;
            }

            // Output format
            if( strtolower( $response_args['output'] ) === 'text')
                foreach($content as $key => $value)
                    $content[$key] = strip_tags($value);

            $content = implode($response_args['glue'], $content);
            
        }
        
        // Dom Parser
        $wpws_parser = new WP_Web_Scraper_Parser( $content, WP_Web_Scraper::$args['charset'] );
        
        // Remove
        if( $response_args['remove_query'] !== '' ){
            if( $response_args['remove_query_type'] === 'regex' )
                $content = preg_replace( $response_args['remove_query'], '', $content);
            if( $response_args['remove_query_type'] === 'cssselector' )
                $content = $wpws_parser->replace_selector( $response_args['remove_query'], '' );
            if( $response_args['remove_query_type'] === 'xpath' )
                $content = $wpws_parser->replace_xpath( $response_args['remove_query'], '' );
            $wpws_parser = new WP_Web_Scraper_Parser( $content, WP_Web_Scraper::$args['charset'] );
        }     
        
        // Replace
        if( $response_args['replace_query'] !== '' ){
            if( $response_args['replace_query_type'] === 'regex' ){
                $replace_query = $response_args['replace_query'];
                $replace_with = $response_args['replace_with'];
                if(is_array( unserialize( urldecode($replace_query) ) ) )
                    $replace_query = unserialize( urldecode($replace_query) );
                if(is_array( unserialize( urldecode($replace_with) ) ) )
                    $replace_with = unserialize( urldecode($replace_with) );            
                $content = preg_replace( $replace_query, $replace_with, $content);
            }
            if( $response_args['replace_query_type'] === 'cssselector' )
                $content = $wpws_parser->replace_selector( $response_args['replace_query'], $response_args['replace_with'] );
            if( $response_args['replace_query_type'] === 'xpath' )
                $content = $wpws_parser->replace_xpath( $response_args['replace_query'], $response_args['replace_with'] );
            $wpws_parser = new WP_Web_Scraper_Parser( $content, WP_Web_Scraper::$args['charset'] );   
        }   
        
        // Basehref
        if( $response_args['basehref'] ){
            if( is_numeric( $response_args['basehref'] ) === false ){
                $base = $response_args['basehref'];
            } else {
                $base = WP_Web_Scraper::$url;
            }
            if( $response_args['basehref'] != 0 ){
                if( $response_args['output'] == 'text' ){
                    $content = str_replace(array('<p>','</p>'), '', $wpws_parser->basehref($base));
                } else {
                    $content =  $wpws_parser->basehref($base);
                }
                $wpws_parser = new WP_Web_Scraper_Parser( $content, WP_Web_Scraper::$args['charset'] ); 
            }
        }     
       
        // a target="_blank"
        if( $response_args['a_target'] )
            $content = $wpws_parser->a_target($response_args['a_target']);

        // Callback
        if( $response_args['callback'] !== '' && is_callable( $response_args['callback'] ) === true )
            $content = call_user_func( $response_args['callback'], $content);        
        
        return $content;
			
	}


	/**
	 * Retrieve the raw response from the HTTP request (or its cached version).
	 * Wrapper function to wp_remote_request()
	 * @param string $url Site URL to retrieve.
	 * @param array $args Optional. Override the defaults.
	 * @return WP_Error|array The response or WP_Error on failure.
	 */	
	public static function remote_request($url, $request_args = array()) {
        
		if( $request_args['headers'] && !empty($request_args['headers']) ) {
			$transient = md5($url.serialize($request_args['headers']));
			parse_str($request_args['headers'], $body);
			$request_args['method'] = 'POST';
			$request_args['body'] = $body;
		} else {
			$transient = md5($url);
		}
		$request_args['user-agent'] = $request_args['useragent'];
		
		if ( get_transient($transient) === false || is_string( get_transient($transient) ) || $request_args['cache'] == 0 ) {
			$response = wp_remote_request($url, $request_args);
			if( !is_wp_error( $response ) ) {
				if($request_args['cache'] != 0)
					set_transient($transient, $response, $request_args['cache'] * 60 );
				@$response['headers']['X-WPWS-Cache-Control'] = 'Remote-fetch via WP_Http';
				return $response;
			} else {
				return new WP_Error('wpws_remote_request_failed', $response->get_error_message());
			}
		} else {
			$cache = get_transient($transient);
			@$cache['headers']['X-WPWS-Cache-Control'] = 'Cache-hit Transients API';
			return $cache;
		}		
		
	}
	
	public static function get_default_args(){
		
		$wpws_options = get_option('wpws_options');
		$default_args = array(
			'headers' => '',
			'cache' => $wpws_options['cache'],
			'useragent' => $wpws_options['useragent'],
			'timeout' => $wpws_options['timeout'],
			'on_error' => $wpws_options['on_error'],
			'output' => 'html',
			'glue' => PHP_EOL,
			'eq' => '',
			'gt' => '',
			'lt' => '',
			'query_type' => 'cssselector',
			'remove_query' => '',
            'remove_query_type' => 'cssselector',
			'replace_query' => '',
            'replace_query_type' => 'cssselector',  
            'replace_with' => '',
            'basehref' => 1,
            'a_target' => '',
            'callback_raw' => '',
			'callback' => '',
			'debug' => 1,
			'charset' => get_bloginfo('charset')			
		);
		return $default_args;
		
	} 

}