<?php

/*
 * Unique identifier for wpws mod used for shortcode creation (wpws_$wpws_mod_name) 
 * and underlying function (wpws_shortcode_$wpws_mod_name). Mod file needs to have
 * at least have wpws_shortcode_$wpws_mod_name to function properly.
 */
$wpws_mod_name = 'market_data';

function wpws_shortcode_market_data($atts) {
	$market_data_array = shortcode_atts( array(
		'market' => '', 
		'symbol' => '', 
		'datatype' => 'last'
	), $atts);
	$wpwsopt = shortcode_atts( array(
		'cache' => 2,
		'user_agent' => get_option('wpws_user_agent'),
		'timeout' => get_option('wpws_timeout'),
		'on_error' => 'wpws_error_show_cache'
	), $atts);
	$url_selector = wpws_market_data_source($market_data_array['market'], $market_data_array['symbol'], $market_data_array['datatype']);
	$wpwsopt = wp_parse_args( $wpwsopt, $url_selector );
	return wpws_get_content($url_selector['url'], $url_selector['selector'], $wpwsopt);
}


function wpws_market_data_source($market, $symbol, $datatype) {
	$market = preg_replace_callback('/___(.*?)___/', create_function('$matches','return $_REQUEST[$matches[1]];'), $market);
	$reuters_market_key = array(
		'nasdaq' => 'O',
		'lse' => 'L',
		'nse' => 'NS',
		'bse' => 'BO'
	);
	$reuters['url'] = 'http://www.reuters.com/finance/stocks/overview?symbol='.$symbol.'.'.$reuters_market_key[$market];
	$reuters['name'] = '#sectionTitle h1';
	$reuters['last'] = '#headerQuoteContainer div.sectionQuoteDetail:eq(0) span:eq(1)';
	$reuters['change_amount'] = '#headerQuoteContainer div.sectionQuoteDetail:eq(1) span:eq(2)';
	$reuters['change_percent'] = '#headerQuoteContainer div.sectionQuoteDetail:eq(1) span:eq(3)';
	$reuters['previous_close'] = '#headerQuoteContainer div.sectionQuote:eq(2) span:eq(1)';
	$reuters['open'] = '#headerQuoteContainer div.sectionQuote:eq(2) span:eq(3)';
	$reuters['high'] = '#headerQuoteContainer div.sectionQuote:eq(3) span:eq(1)';
	$reuters['low'] = '#headerQuoteContainer div.sectionQuote:eq(3) span:eq(3)';
	$reuters['volume'] = '#headerQuoteContainer div.sectionQuote:eq(4) span:eq(1)';
	$reuters['52_week_high'] = '#headerQuoteContainer div.sectionQuote:eq(5) span:eq(1)';
	$reuters['52_week_low'] = '#headerQuoteContainer div.sectionQuote:eq(5) span:eq(3)';
	$reuters['chart'] = '#overviewChart a';
	
	return array(
		'url' => $reuters['url'],
		'selector' => $reuters[$datatype],
		'clear_regex' => '/\(|\)|'.strtoupper($symbol.'.'.$reuters_market_key[$market]).'/'
	);
}

?>