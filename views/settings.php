<div class="wrap">
    <h2><?php _e( 'WP Web Scraper' , 'wp-web-scraper' )?></h2>
    
    <h2 class="nav-tab-wrapper">
        <a class="nav-tab <?php echo ($_GET['tab'] === 'settings' ? 'nav-tab-active' : '')?>" href="options-general.php?page=wp_web_scraper&tab=settings"><?php _e( 'Settings' , 'wp-web-scraper' )?></a>
        <a class="nav-tab <?php echo ($_GET['tab'] === 'sandbox' ? 'nav-tab-active' : '')?>" href="options-general.php?page=wp_web_scraper&tab=sandbox"><?php _e( 'Sandbox' , 'wp-web-scraper' )?></a>
        <a class="nav-tab <?php echo ($_GET['tab'] === 'import' ? 'nav-tab-active' : '')?>" href="options-general.php?page=wp_web_scraper&tab=import"><?php _e( 'Import' , 'wp-web-scraper' )?></a>
    </h2>    
    
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<div id="post-body-content">

            <?php if($_GET['tab'] === 'settings'): ?>	
                <form method="post" action="options.php"> 
                    <?php @settings_fields('wpws_options'); ?>

                    <?php @do_settings_sections('wp_web_scraper_settings'); ?>

                    <?php @submit_button(); ?>
                </form>
            <?php endif; ?>	

            <?php if($_GET['tab'] === 'sandbox'): ?>
                <form method="post" action="?page=wp_web_scraper&tab=sandbox">
                  <?php wp_nonce_field('wpws-sandbox') ?>
                  <p><?php _e( 'A simple testing &amp; shortcode building tool for WP Web Scraper' , 'wp-web-scraper' )?></p>
                    <table class="form-table">
                        <tbody>
                            <tr><th scope="row"><?php _e( 'Source URL' , 'wp-web-scraper' )?></th><td><fieldset><input name="url" type="text" id="url" class="large-text" value="<?php echo $_POST['url']?>"></fieldset></td></tr>
                            <tr><th scope="row"><?php _e( 'Query' , 'wp-web-scraper' )?></th><td><fieldset><input name="query" type="text" id="query" class="large-text" value="<?php echo $_POST['query']?>"><p class="description"><?php _e( '<a href="http://wp-ws.net/docs/query/" target="_blank">CSS selector, XPath or Regex query</a> to fetch data.' , 'wp-web-scraper' )?></p></fieldset></td></tr>
                            <tr><th scope="row"><?php _e( 'Other Arguments' , 'wp-web-scraper' )?></th><td><fieldset><input name="args" type="text" id="args" class="large-text" value="<?php echo $_POST['args']?>" autocomplete="off"><p class="description"><?php _e( '<a href="http://wp-ws.net/docs/arguments-api/" target="_blank">Arguments</a> in http query string format.' , 'wp-web-scraper' )?> <a data-args="<?php http_build_query($default_args)?>" id="load_default_args"><?php _e( 'Load defaults.' , 'wp-web-scraper' )?></a></p></fieldset></td></tr>
                        </tbody>
                    </table>
                    <?php @submit_button( __( 'Test Scrap', 'wp-web-scraper' ) ); ?>
                </form>

                <?php if(isset($result_output)) : ?>
                <div id="wpws-sandbox" class="categorydiv">
                    <ul id="sandbox-tabs" class="sandbox-tabs">
                        <li class="tabs"><a href="#output"><?php _e( 'Output' , 'wp-web-scraper' )?></a></li>
                        <li class="hide-if-no-js tabs"><a href="#shortcode"><?php _e( 'Shortcode' , 'wp-web-scraper' )?></a></li>	
                        <li class="hide-if-no-js tabs"><a href="#tt"><?php _e( 'Template Tag' , 'wp-web-scraper' )?></a></li>	
                        <li class="hide-if-no-js tabs"><a href="#debug"><?php _e( 'Debug info' , 'wp-web-scraper' )?></a></li>
                    </ul>

                    <div id="output" class="tabs-panel">
                        <?php echo $result_output?>
                    </div>		

                    <div id="shortcode" class="tabs-panel">
                    <p class="description"><?php _e( 'Make sure you paste this in the Text mode in the post editor' , 'wp-web-scraper' )?></p>
                    <br />
                    <code>
                    [wpws url="<?php echo $result_url_shortcode?>" query="<?php echo $result_query_shortcode?>" 
                    <?php foreach($modified_args_shortcode as $key => $value) : ?>
                        <?php echo $key?>="<?php echo $value?>"
                    <?php endforeach; ?>
                    ]
                    </code>
                    </div>

                    <div id="tt" class="tabs-panel">
                    <p class="description"><?php _e( 'Template tag for use in your theme' , 'wp-web-scraper' )?></p>
                    <br />
                    <code>
                    &lt;?php echo wpws_get_content('<?php echo $result_url?>', '<?php echo $result_query?>' 
                    <?php echo (count($modified_args_tt) > 0 ? ', array(' : '')?>
                    <?php foreach($modified_args_tt as $key => $value) : ?>
                        '<?php echo $key?>' => '<?php echo $value?>',
                    <?php endforeach; ?>
                    <?php echo (count($modified_args_tt) > 0 ? ')' : '')?> 
                    ); ?&gt;
                    </code>
                    </div>      

                    <div id="debug" class="tabs-panel">
                        <h4><?php _e( 'Scrap source and info' , 'wp-web-scraper' )?></h4>
                        <table class="args">
                            <tr><td class="key"><?php _e( 'Source URL' , 'wp-web-scraper' )?></td><td class="value"><a href="<?php echo $result_url?>" target="_blank"><?php echo $result_url?></a></td></tr>
                            <tr><td class="key"><?php _e( 'Query' , 'wp-web-scraper' )?> (<?php echo $result_args['query_type']?>)</td><td class="value"><?php echo $result_query?></td></tr>
                            <tr><td class="key"><?php _e( 'WPWS Cache Control' , 'wp-web-scraper' )?></td><td class="value"><?php echo $result_xcache?></td></tr>
                        </table>
                        <h4><?php _e( 'Other arguments' , 'wp-web-scraper' )?></h4>
                        <table class="args">
                        <?php foreach($result_args as $key => $value) : ?>
                            <tr>
                              <td class="key"><?php echo $key?></td>
                              <td class="value"><?php var_dump($value)?></td>
                            </tr>
                        <?php endforeach; ?>
                        </table>
                    </div>
                </div>
                <?php endif; ?>	

            <?php endif; ?>	

            <?php if($_GET['tab'] === 'import'): ?>
            <?php if(is_int($post_id)): ?><div id="message" class="updated below-h2"><p>Post published. <a href="<?php echo get_permalink( $post_id )?>" target="_blank">View Post</a>. <?php echo edit_post_link( 'Edit post', '', '', $post_id )?>.</p></div><?php endif; ?>
            <?php if($post_id === false): ?><div id="message" class="updated error below-h2"><p>Error importing and posting.</p></div><?php endif; ?>
                <form method="post" action="?page=wp_web_scraper&tab=import">
                <?php wp_nonce_field('wpws-import') ?>
                <p><?php _e( 'A simple import utility to create posts using data scraped by WP Web Scraper' , 'wp-web-scraper' )?></p>
                <table class="form-table">
                    <tbody>
                        <tr>
                          <th scope="row"><label for="post_sc_url">URL</label></th>
                          <td><input name="post_sc_url" type="url" id="post_sc_url" class="large-text code" value="<?php echo $wpws_last_import['post_sc_url'] ?>">
                          <p class="description">If used this will override the URL param of all WPWS shortcodes in Title, Content and Tags</p></td>
                        </tr>                      
                        <tr>
                          <th scope="row"><label for="post_title_sc">Title</label><p class="description">Shortcodes supported</p></th>
                          <td><input name="post_title_sc" type="text" id="post_title_sc" class="large-text code" required="required" value="<?php echo @htmlspecialchars($wpws_last_import['post_title_sc']) ?>"></td>
                        </tr>
                        <tr>
                          <th scope="row"><label for="post_content_sc">Content</label><p class="description">Shortcodes supported</p></th>
                          <td><?php wp_editor( $wpws_last_import['post_content_sc'], 'postcontentsc', array( 'textarea_name' => 'post_content_sc', 'textarea_rows' => 10 ) ); ?>
                            <fieldset>
                              <label><input type="checkbox" name="post_content_sc_mode" value="shortcode" <?php checked( @$wpws_last_import['post_content_sc_mode'], 'shortcode')?>> <span>Post as shortcode to keep it dynamic</span></label><br>
                            </fieldset>                
                          </td>
                        </tr>
                        <tr>
                          <th scope="row"><label for="tags_input_sc">Tags</label><p class="description">Shortcodes supported</p></th>
                          <td><input name="tags_input_sc" type="text" id="tags_input_sc" class="large-text code" value="<?php echo @htmlspecialchars($wpws_last_import['tags_input_sc']) ?>">
                            <p class="description">Commas in the output are used as separators</p></td>
                        </tr>                        
                        <tr>
                          <th scope="row">Categories</th>
                          <td>
                            <ul class="cat-checklist category-checklist">
                              <?php wp_category_checklist(); ?>
                            </ul>
                          </td>
                        </tr>
                        <tr>
                          <th scope="row"><label for="post_status">Post Status</label></th>
                          <td>
                            <select name="post_status">
                              <option value="publish" <?php selected( $wpws_last_import['post_status'], 'publish' ); ?>>Publish</option>
                              <option value="draft" <?php selected( $wpws_last_import['post_status'], 'draft' ); ?>>Draft</option>
                              <option value="pending" <?php selected( $wpws_last_import['post_status'], 'pending' ); ?>>Pending Review</option>
                              <option value="private" <?php selected( $wpws_last_import['post_status'], 'private' ); ?>>Private</option>
                            </select>
                          </td>
                        </tr>                        
                        <tr>
                          <th scope="row">Author</th>
                          <td>
                            <?php wp_dropdown_users(array('name' => 'post_author')); ?>
                          </td>
                        </tr>

                    </tbody>
                </table>
                <?php @submit_button( __( 'Import and Post', 'wp-web-scraper' ) ); ?>
                </form>
            <?php endif; ?>    

			</div>

			<div id="postbox-container-1" class="postbox-container">
				<div id="formatdiv" class="postbox ">
					<h3 class="hndle"><span><?php _e( 'Quick Help' , 'wp-timesheets' )?></span></h3>
					<div class="inside">
                        <p>Get started with reading more about <a href="http://wp-ws.net/faqs/what-is-web-scraping-needed-in-wordpress/" target="_blank">Why is web scraping needed in WordPress?</a> and <a href="http://wp-ws.net/faqs/how-to-use-wp-web-scraper/" target="_blank">How to use WP Web Scraper?</a></p>
                        <p>Visit <a href="http://wp-ws.net/" target="_blank">wp-ws.net</a> for elaborate <a href="http://wp-ws.net/docs/" target="_blank">documentation</a> on <a href="http://wp-ws.net/docs/query/" target="_blank">Query</a>, <a href="http://wp-ws.net/docs/arguments-api/" target="_blank">arguments</a>, and core features like <a href="http://wp-ws.net/docs/dynamic-url-headers/" target="_blank">Dynamic URLs</a> and <a href="http://wp-ws.net/docs/callback-functions/" target="_blank">Callbacks</a></p>
                        <p>You will also find a bunch of <a href="http://wp-ws.net/examples/" target="_blank">examples</a> and answers to <a href="http://wp-ws.net/faqs/" target="_blank">FAQs</a> there to help you get started.</p>
                        <p>For generic support, reporting issues, bugs etc, you may use <a href="https://wordpress.org/support/plugin/wp-web-scrapper" target="_blank">WP Web Scraper Support on wordpress.org</a>.</p>
                        <h4>Use this <a href="http://wp-ws.net/support/" target="_blank">form for paid support on building a query or a callback function</a></h4>
					</div>
				</div>
			</div>

		</div> <!-- #post-body -->
	</div> <!-- #poststuff -->    
    
</div>