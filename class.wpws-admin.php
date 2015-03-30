<?php

class WP_Web_Scraper_Admin {
	
	public static $settings;

	public static function init() {
		
		add_action( 'admin_init', array( 'WP_Web_Scraper_Admin', 'admin_init' ) );
		add_action( 'admin_menu', array( 'WP_Web_Scraper_Admin', 'admin_menu' ) );

	}

	public static function admin_init() {
		
		// Load text domain
		load_plugin_textdomain( 'wp_web_scraper' );
		
		// Load settings
		WP_Web_Scraper_Admin::$settings = 
		array(
			'sections' => array(
				array( 'id' => 'section_enable', 'title' => '', 
					'callback' => array('WP_Web_Scraper_Admin', 'section_enable_cb'), 'page' => 'wp_web_scraper_settings' ),
				array( 'id' => 'section_defaults', 'title' => __('Defaults', 'wp-web-scraper'), 
					'callback' => array('WP_Web_Scraper_Admin', 'section_defaults_cb'), 'page' => 'wp_web_scraper_settings' ),				
			),
			'fields' => array(
				array( 'id' => 'enable', 'title' => __('Enable WP Web Scraper', 'wp-web-scraper'), 
					'callback' => array('WP_Web_Scraper_Admin', 'fields_cb'), 'page' => 'wp_web_scraper_settings', 
					'section' => 'section_enable',  
					'args' => array( 'type' => 'group', 'data' => 'other_fields' ) ),
				array( 'id' => 'on_error', 'title' => __('Error Handling', 'wp-web-scraper'), 
					'callback' => array('WP_Web_Scraper_Admin', 'fields_cb'), 'page' => 'wp_web_scraper_settings', 
					'section' => 'section_defaults', 
					'args' => array( 'id' => 'on_error', 'type' => 'select', 
						'options' => array( 
							array('value' => 'error_hide', 'text' => __('Fail silently (no output on failure)', 'wp-web-scraper') ), 
							array('value' => 'error_show', 'text' => __('Display error details', 'wp-web-scraper') ) ), 
						'description' => __('Error display handling. Fail silently or display error.', 'wp-web-scraper') ) ),
				array( 'id' => 'useragent', 'title' => __('Useragent string', 'wp-web-scraper'), 
					'callback' => array('WP_Web_Scraper_Admin', 'fields_cb'), 'page' => 'wp_web_scraper_settings', 
					'section' => 'section_defaults', 
					'args' => array( 'id' => 'useragent', 'type' => 'text', 
						'description' => __('HTTP useragent header to identify yourself when crawling sites.', 'wp-web-scraper') ) ),
				array( 'id' => 'timeout', 'title' => __('Timeout (seconds)', 'wp-web-scraper'), 
					'callback' => array('WP_Web_Scraper_Admin', 'fields_cb'), 'page' => 'wp_web_scraper_settings', 
					'section' => 'section_defaults', 
					'args' => array( 'id' => 'timeout', 'type' => 'number', 'step' => 1, 'min' => 1, 
						'description' => __('Timeout seconds for HTTP request. <a href="http://wpws.in/faqs/how-to-optimize-performance/" target="_blank">Larger interval may impact pageload</a>.', 'wp-web-scraper') ) ),
				array( 'id' => 'cache', 'title' => __('Cache expiration (minutes)', 'wp-web-scraper'), 
					'callback' => array('WP_Web_Scraper_Admin', 'fields_cb'), 'page' => 'wp_web_scraper_settings', 
					'section' => 'section_defaults', 
					'args' => array( 'id' => 'cache', 'type' => 'number', 'step' => 10, 'min' => 0, 
						'description' => __('Cache expiration minutes for cached webpages. <br />Strongly recommended to <a href="http://wpws.in/faqs/how-to-optimize-performance/" target="_blank">use a Persistent Cache Plugin for better caching performance</a>.', 'wp-web-scraper') ) ),				
			),
			'other_fields' => array(
				array( 'id' => 'sc_posts',  
					'args' => array( 'id' => 'sc_posts', 'type' => 'checkbox', 'text' => __('Enable shortcode in posts, pages', 'wp-web-scraper') ) ),
				array( 'id' => 'sc_widgets', 
					'args' => array( 'id' => 'sc_widgets', 'type' => 'checkbox', 'text' => __('Enable shortcode in widgets', 'wp-web-scraper') ) ),
				array( 'id' => 'tt',  
					'args' => array( 'id' => 'tt', 'type' => 'checkbox', 'text' => __('Enable template tag', 'wp-web-scraper') ) )				
			),
			'option_group' => 'wpws_options'
		);
		WP_Web_Scraper_Admin::settings_api( WP_Web_Scraper_Admin::$settings );
		
		// Settings page link
		add_filter( 'plugin_action_links_' . WPWS__PLUGIN_FILE, array('WP_Web_Scraper_Admin', 'plugin_settings_link'), 10, 2 );		
		
	}
	
	public static function settings_api( $settings ) {
		foreach ($settings['sections'] as $section)
			add_settings_section( $section['id'], $section['title'], $section['callback'], $section['page'] );
		foreach ($settings['fields'] as $field)
			add_settings_field( $field['id'], $field['title'], $field['callback'], $field['page'], $field['section'], $field['args'] );
		register_setting( $settings['option_group'], $settings['option_group'] );
	}
	
	public static function section_enable_cb() {
		
	}
	
	public static function section_defaults_cb() {
		_e('These settings are used as default <a href="http://wpws.in/docs/arguments-api/" target="_blank">arguments</a>, but can also be overwritten in WP Web Scraper shortcodes and template tags', 'wp-web-scraper');
	}	
	
	public static function fields_cb( $option ) {
		
		$wpws_options = get_option( WP_Web_Scraper_Admin::$settings['option_group'] );
		
		$options = array( $option );
		if( $option['type'] === 'group' )
			foreach (WP_Web_Scraper_Admin::$settings[$option['data']] as $custom_field)
				$options[] = $custom_field['args'];
		
		echo '<fieldset>';
		foreach ($options as $option) {
			if( $option['type'] === 'text' ){
				echo '<input name="'.WP_Web_Scraper_Admin::$settings['option_group'].'['.$option['id'].']" type="text" id="'.$option['id'].'" class="regular-text" value="'.$wpws_options[$option['id']].'" />';
			}
			if( $option['type'] === 'number' ){
				echo '<input name="'.WP_Web_Scraper_Admin::$settings['option_group'].'['.$option['id'].']" type="number" id="'.$option['id'].'" step="'.$option['step'].'" min="'.$option['min'].'" class="small-text" value="'.$wpws_options[$option['id']].'" />';
			}
			if( $option['type'] === 'checkbox' ){
				echo '<label><input name="'.WP_Web_Scraper_Admin::$settings['option_group'].'['.$option['id'].']" type="checkbox" id="'.$option['id'].'" value="1" '.checked( 1, $wpws_options[$option['id']], false ).' /> '.$option['text'].'</label><br />';
			}		
			if( $option['type'] === 'select' ){
				echo '<select name="'.WP_Web_Scraper_Admin::$settings['option_group'].'['.$option['id'].']" id="'.$option['id'].'">';
				foreach ($option['options'] as $value)
					echo '<option value="'.$value['value'].'" '.selected( $value['value'], $wpws_options[$option['id']], false ).'>'.$value['text'].'</option>';
				echo '</select>';
			}
			if(isset($option['description'])) echo '<p class="description">'.$option['description'].'</p>';			
		}
		echo '</fieldset>';
		
	}
	
	public static function admin_menu() {
		
		$page_hook_suffix = add_options_page(
			__('WP Web Scraper Settings', 'wp-web-scraper'), 
			__('WP Web Scraper', 'wp-web-scraper'), 
			'manage_options', 
			'wp_web_scraper', 
			array('WP_Web_Scraper_Admin', 'plugin_settings_page')
		);
		add_action('admin_print_scripts-' . $page_hook_suffix, array( 'WP_Web_Scraper_Admin', 'admin_scripts' ));
		
	}
	
	public static function admin_scripts(){
		
		wp_enqueue_script( 'wpws-js', plugins_url( '/views/js/wpws.js', __FILE__ ), array('jquery-ui-tabs'), WPWS__VERSION );
		wp_enqueue_style( 'wpws-css', plugins_url( '/views/css/wpws.css', __FILE__ ), array(), WPWS__VERSION );
		
	}

	public static function plugin_settings_page() {
		if(!isset($_GET['tab'])) 
			$_GET['tab'] = 'settings';
		$vars['default_args'] = WP_Web_Scraper::get_default_args();
        $vars['default_args']['urldecode'] = 1;
        $vars['default_args']['querydecode'] = 0;
        
        // Settings controler
		if($_GET['tab'] === 'sandbox' && !empty($_POST) && wp_verify_nonce( $_POST['_wpnonce'], 'wpws-sandbox' ) !== false){       
            
			$vars['result_output'] = WP_Web_Scraper::get_content( $_POST['url'], $_POST['query'], $_POST['args'] );
			$vars['result_url'] = WP_Web_Scraper::$url;
            $vars['result_query'] = WP_Web_Scraper::$query;
			$vars['result_args'] = WP_Web_Scraper::$args;
            if( strpos($_POST['url'], '<') !== false || strpos($_POST['url'], '>') !== false || strpos($_POST['url'], '[') !== false || strpos($_POST['url'], ']') !== false ){
                $vars['result_url_shortcode'] = str_replace(array('<','>','[',']'), array('%3c','%3e','%5b','%5d'), WP_Web_Scraper::$url);
                $vars['result_args']['urldecode'] = 1;
            } else {
                $vars['result_url_shortcode'] = $vars['result_url'];
            }               
            if( strpos($_POST['query'], '<') !== false || strpos($_POST['query'], '>') !== false || strpos($_POST['query'], '[') !== false || strpos($_POST['query'], ']') !== false ){
                $vars['result_query_shortcode'] = str_replace(array('<','>','[',']'), array('%3c','%3e','%5b','%5d'), WP_Web_Scraper::$query);
                $vars['result_args']['querydecode'] = 1;
            } else {
                $vars['result_query_shortcode'] = $vars['result_query'];
            }           
            $vars['modified_args_shortcode'] = array_diff_assoc($vars['result_args'], $vars['default_args']);
            $vars['modified_args_tt'] = array_diff_assoc(WP_Web_Scraper::$args, WP_Web_Scraper::get_default_args());
			$vars['result_xcache'] = WP_Web_Scraper::$xcache;
		}
        
        // Import controler
        if($_GET['tab'] === 'import' && !empty($_POST) && wp_verify_nonce( $_POST['_wpnonce'], 'wpws-import' ) !== false){ 
            update_option('wpws_last_import', stripslashes_deep($_POST));
            $url_str = '';
            if(!empty($_POST['post_sc_url']))
                $url_str = ' url="'.$_POST['post_sc_url'].'"';
            $post = $_POST;
            $post_content_sc = stripslashes_deep( htmlspecialchars_decode($_POST['post_content_sc']) );
            $post_title_sc = str_replace(']', $url_str.' debug="0" on_error="error_hide" output="text"]', stripslashes_deep( htmlspecialchars_decode($_POST['post_title_sc']) ) );
            $tags_input_sc = str_replace(']', $url_str.' debug="0" on_error="error_hide" output="text" glue=","]', stripslashes_deep( htmlspecialchars_decode($_POST['tags_input_sc']) ) );
            if($_POST['post_content_sc_mode'] == 'shortcode'){
                $post['post_content'] = str_replace(']', $url_str.']', $post_content_sc);
            } else {
                $post_content_sc = str_replace(']', $url_str.' debug="0" on_error="error_hide"]', $post_content_sc);
                $post['post_content'] = trim( do_shortcode( $post_content_sc ) );
            }
            $post['post_title'] = trim( do_shortcode( $post_title_sc ) );
            $post['tags_input'] = trim( do_shortcode( $tags_input_sc ) );
            //print_r($post);
            if(!empty($post['post_title']) && !empty($post['post_title']))
                $post_id = wp_insert_post( $post, true );
            if ( isset( $post_id ) && !is_wp_error( $post_id  ) ) {
                $vars['post_id'] = $post_id;
            } else {
                $vars['post_id'] = false;
            }          
        }
        if($_GET['tab'] === 'import')
            $vars['wpws_last_import'] = get_option('wpws_last_import');        
        
        // Render view
		WP_Web_Scraper::view( 'settings', $vars );
	}

	public static function plugin_settings_link($links) {
		$settings_link = '<a href="options-general.php?page=wp_web_scraper">' . __('Settings', 'wp-web-scraper') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

}