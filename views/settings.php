<div class="wrap">
    <h2><?=_e( 'WP Web Scraper' , 'wp-web-scraper' )?></h2>
	<h2 class="nav-tab-wrapper">
		<a class="nav-tab <?=$_GET['tab'] === 'settings' ? 'nav-tab-active' : ''?>" href="options-general.php?page=wp_web_scraper&tab=settings"><?=_e( 'Settings' , 'wp-web-scraper' )?></a>
		<a class="nav-tab <?=$_GET['tab'] === 'sandbox' ? 'nav-tab-active' : ''?>" href="options-general.php?page=wp_web_scraper&tab=sandbox"><?=_e( 'Sandbox' , 'wp-web-scraper' )?></a>
		<a class="nav-tab <?=$_GET['tab'] === 'help' ? 'nav-tab-active' : ''?>" href="options-general.php?page=wp_web_scraper&tab=help"><?=_e( 'Help' , 'wp-web-scraper' )?></a>
	</h2>
	
<?php if($_GET['tab'] === 'settings'): ?>	
    <form method="post" action="options.php"> 
        <?php @settings_fields('wpws_options'); ?>

        <?php @do_settings_sections('wp_web_scraper_settings'); ?>

        <?php @submit_button(); ?>
    </form>
<?php endif; ?>	
	
<?php if($_GET['tab'] === 'sandbox'): ?>
    <form method="post" action="?page=wp_web_scraper&tab=sandbox">
		<h3></h3>
		<?=_e( 'A simple testing &amp; shortcode building tool for WP Web Scraper' , 'wp-web-scraper' )?>
		<table class="form-table">
			<tbody>
				<tr><th scope="row"><?=_e( 'Source URL' , 'wp-web-scraper' )?></th><td><fieldset><input name="url" type="text" id="url" class="large-text" value="<?=$_POST['url']?>"></fieldset></td></tr>
				<tr><th scope="row"><?=_e( 'Query' , 'wp-web-scraper' )?></th><td><fieldset><input name="query" type="text" id="query" class="large-text" value="<?=$_POST['query']?>"><p class="description"><?=_e( '<a href="http://wpws.in/docs/query/" target="_blank">CSS selector, XPath or Regex query</a> to fetch data.' , 'wp-web-scraper' )?></p></fieldset></td></tr>
				<tr><th scope="row"><?=_e( 'Other Arguments' , 'wp-web-scraper' )?></th><td><fieldset><input name="args" type="text" id="args" class="large-text" value="<?=$_POST['args']?>" autocomplete="off"><p class="description"><?=_e( '<a href="http://wpws.in/docs/arguments-api/" target="_blank">Arguments</a> in http query string format.' , 'wp-web-scraper' )?> <a data-args="<?=http_build_query($default_args)?>" id="load_default_args"><?=_e( 'Load defaults.' , 'wp-web-scraper' )?></a></p></fieldset></td></tr>
			</tbody>
		</table>
		<?php @submit_button( __( 'Test Scrap', 'wp-web-scraper' ) ); ?>
    </form>

    <?php if(isset($result_output)) : ?>
	<div id="wpws-sandbox" class="categorydiv">
		<ul id="sandbox-tabs" class="sandbox-tabs">
			<li class="tabs"><a href="#output"><?=_e( 'Output' , 'wp-web-scraper' )?></a></li>
			<li class="hide-if-no-js tabs"><a href="#shortcode"><?=_e( 'Shortcode' , 'wp-web-scraper' )?></a></li>	
            <li class="hide-if-no-js tabs"><a href="#tt"><?=_e( 'Template Tag' , 'wp-web-scraper' )?></a></li>	
			<li class="hide-if-no-js tabs"><a href="#debug"><?=_e( 'Debug info' , 'wp-web-scraper' )?></a></li>
		</ul>
		
		<div id="output" class="tabs-panel">
			<?=$result_output?>
		</div>		

		<div id="shortcode" class="tabs-panel">
        <p class="description"><?=_e( 'Make sure you paste this in the Text mode in the post editor' , 'wp-web-scraper' )?></p>
        <br />
        <code>
        [wpws url="<?=$result_url_shortcode?>" query="<?=$result_query_shortcode?>" 
        <?php foreach($modified_args_shortcode as $key => $value) : ?>
            <?=$key?>="<?=$value?>"
        <?php endforeach; ?>
        ]
        </code>
        </div>
      
		<div id="tt" class="tabs-panel">
        <p class="description"><?=_e( 'Template tag for use in your theme' , 'wp-web-scraper' )?></p>
        <br />
        <code>
        &lt;?php echo wpws_get_content('<?=$result_url?>', '<?=$result_query?>' 
        <?=(count($modified_args_tt) > 0 ? ', array(' : '')?>
        <?php foreach($modified_args_tt as $key => $value) : ?>
            '<?=$key?>' => '<?=$value?>',
        <?php endforeach; ?>
        <?=(count($modified_args_tt) > 0 ? ')' : '')?> 
        ?&gt;
        </code>
		</div>      
		
		<div id="debug" class="tabs-panel">
			<h4><?=_e( 'Scrap source and info' , 'wp-web-scraper' )?></h4>
			<table class="args">
				<tr><td class="key"><?=_e( 'Source URL' , 'wp-web-scraper' )?></td><td class="value"><a href="<?=$result_url?>" target="_blank"><?=$result_url?></a></td></tr>
				<tr><td class="key"><?=_e( 'Query' , 'wp-web-scraper' )?> (<?=$result_args['query_type']?>)</td><td class="value"><?=$result_query?></td></tr>
				<tr><td class="key"><?=_e( 'WPWS Cache Control' , 'wp-web-scraper' )?></td><td class="value"><?=$result_xcache?></td></tr>
			</table>
			<h4><?=_e( 'Other arguments' , 'wp-web-scraper' )?></h4>
			<table class="args">
			<?php foreach($result_args as $key => $value) : ?>
				<tr>
                  <td class="key"><?=$key?></td>
                  <td class="value"><?=var_dump($value)?></td>
                </tr>
			<?php endforeach; ?>
			</table>
		</div>
	</div>
    <?php endif; ?>	
	
<?php endif; ?>	
    
<?php if($_GET['tab'] === 'help'): ?>	
    <p><?=_e( 'WP Web Scraper is an easy to implement web scraper for WordPress that lets you display realtime data from any websites directly into your posts, pages or sidebar. Using a shortcode or a template tag, it lets you specify a URL and a query to display your desired content.', 'wp-web-scraper' )?></p>
    <p><?=_e( 'You may start with reading more about \'<a href="http://wpws.in/faqs/what-is-web-scraping-needed-in-wordpress/" target="_blank">Why is web scraping needed in WordPress?</a>\' and \'<a href="http://wpws.in/faqs/how-to-use-wp-web-scraper/" target="_blank">How to use WP Web Scraper?</a>\'', 'wp-web-scraper' )?></p>
    <p><?=_e( 'Visit <a href="http://wpws.in/" target="_blank">wpws.in</a> for elaborate <a href="http://wpws.in/docs/" target="_blank">documentation</a> on <a href="http://wpws.in/docs/query/" target="_blank">Query</a>, <a href="http://wpws.in/docs/arguments-api/" target="_blank">arguments</a>, and core features like <a href="http://wpws.in/docs/dynamic-url-headers/" target="_blank">Dynamic URLs</a> and <a href="http://wpws.in/docs/callback-functions/" target="_blank">Callbacks</a>.' , 'wp-web-scraper' )?>
    <p><?=_e( 'You will also find a bunch of <a href="http://wpws.in/examples/" target="_blank">examples</a> and answers to <a href="http://wpws.in/faqs/" target="_blank">FAQs</a> there to help you get started.' , 'wp-web-scraper' )?></p>
    <p><?=_e( 'For generic support, reporting issues, bugs etc, you may use <a href="https://wordpress.org/support/plugin/wp-web-scrapper" target="_blank">WP Web Scraper Support on wordpress.org</a>.' , 'wp-web-scraper' )?></p>
    <div id="setting-error-settings_updated" class="updated settings-error"> 
        <p><strong><?=_e( 'Use this <a href="http://wpws.in/support/" target="_blank">form for paid support on building a query or a callback function</a>' , 'wp-web-scraper' )?></strong></p>
    </div>         
<?php endif; ?>	    
</div>