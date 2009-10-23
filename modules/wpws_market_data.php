<?php

if(get_option('wpws_sc_posts') == 1) add_shortcode('wpws_market_data', 'wpws_market_data_shortcode');

function wpws_market_data_shortcode($atts) {
	extract(shortcode_atts(array('market' => '', 'symbol' => '', 'datatype' => '', 'cache' => 1, 'agent' => get_option('wpws_curl_agent'), 'timeout' => 2, 'error' => 'cache'), $atts));
	if($market == '' || $symbol == '') {
		if($curl_error == '1') {return 'Required params missing';}
		elseif($curl_error == '0') {return false;} 
		else {return $curl_error;}	
	} else {
		if($datatype == '') {$datatype = 'last';} 
		$url = wpws_market_data_source($market, $symbol, $datatype, 'url');
		$selector = wpws_market_data_source($market, $symbol, $datatype, 'selector');
		$clear = '/\$| \(Nasdaq\)|Rs/';
		$output = 'text';
		return wpws_get_content($url, $selector, $clear, '', '', '', $output, $cache, $agent, $timeout, $error);
	}
}

function wpws_market_data_source($market, $symbol, $datatype, $return) {
	$wpws_md_array = array();
	
	/* NSE market data definition array */
	$wpws_md_array['nse']['url'] = 'http://nseindia.com/marketinfo/equities/cmquote_tab.jsp?key='.strtoupper($symbol).'EQN&symbol='.strtoupper($symbol).'&flag=0';
	$wpws_md_array['nse']['name'] = 'table:eq(3) td:eq(0)';
	$wpws_md_array['nse']['timestamp'] = '';
	$wpws_md_array['nse']['52_week_high'] = 'table:eq(3) td:eq(3)';
	$wpws_md_array['nse']['52_week_low'] = 'table:eq(3) td:eq(4)';	
	$wpws_md_array['nse']['open'] = 'td .t1:eq(3)';
	$wpws_md_array['nse']['high'] = 'td .t1:eq(4)';
	$wpws_md_array['nse']['low'] = 'td .t1:eq(5)';
	$wpws_md_array['nse']['last'] = 'td .t1:eq(6)';
	$wpws_md_array['nse']['previous_close'] = 'td .t1:eq(7)';
	$wpws_md_array['nse']['change_amount'] = 'td .t1:eq(8)';
	$wpws_md_array['nse']['change_percent'] = 'td .t1:eq(9)';
	$wpws_md_array['nse']['average'] = 'td .t1:eq(10)';
	$wpws_md_array['nse']['traded_quantity'] = 'td .t1:eq(11)';
	$wpws_md_array['nse']['turnover'] = 'td .t1:eq(12)';
	
	/* NASDAQ market data definition array */
	$wpws_md_array['nasdaq']['url'] = 'http://www.reuters.com/finance/stocks/overview?symbol='.strtoupper($symbol).'.O';
	$wpws_md_array['nasdaq']['name'] = '.quoteHeader h5';
	$wpws_md_array['nasdaq']['timestamp'] = '';
	$wpws_md_array['nasdaq']['52_week_high'] = '#quoteDetail .sectionHalf div:eq(4) span';
	$wpws_md_array['nasdaq']['52_week_low'] = '#quoteDetail .sectionHalf div:eq(5) span';
	$wpws_md_array['nasdaq']['open'] = '#quoteDetail .sectionHalf div:eq(1) span';
	$wpws_md_array['nasdaq']['high'] = '#quoteDetail .sectionHalf div:eq(2) span';
	$wpws_md_array['nasdaq']['low'] = '#quoteDetail .sectionHalf div:eq(3) span';
	$wpws_md_array['nasdaq']['last'] = '#priceQuote .value .valueContent';
	$wpws_md_array['nasdaq']['previous_close'] = '#quoteDetail .sectionHalf div:eq(0) span';
	$wpws_md_array['nasdaq']['change_amount'] = '#priceChange .value .valueContent';
	$wpws_md_array['nasdaq']['change_percent'] = '#percentChange .value .valueContent';
	$wpws_md_array['nasdaq']['average'] = '';
	$wpws_md_array['nasdaq']['traded_quantity'] = '';
	$wpws_md_array['nasdaq']['turnover'] = '';
	
	/* BSE market data definition array */
	$wpws_md_array['bse']['url'] = 'http://www.reuters.com/finance/stocks/overview?symbol='.strtoupper($symbol).'.BO';
	$wpws_md_array['bse']['name'] = '.quoteHeader h5';
	$wpws_md_array['bse']['timestamp'] = '';
	$wpws_md_array['bse']['52_week_high'] = '#quoteDetail .sectionHalf div:eq(4) span';
	$wpws_md_array['bse']['52_week_low'] = '#quoteDetail .sectionHalf div:eq(5) span';
	$wpws_md_array['bse']['open'] = '#quoteDetail .sectionHalf div:eq(1) span';
	$wpws_md_array['bse']['high'] = '#quoteDetail .sectionHalf div:eq(2) span';
	$wpws_md_array['bse']['low'] = '#quoteDetail .sectionHalf div:eq(3) span';
	$wpws_md_array['bse']['last'] = '#priceQuote .value .valueContent';
	$wpws_md_array['bse']['previous_close'] = '#quoteDetail .sectionHalf div:eq(0) span';
	$wpws_md_array['bse']['change_amount'] = '#priceChange .value .valueContent';
	$wpws_md_array['bse']['change_percent'] = '#percentChange .value .valueContent';
	$wpws_md_array['bse']['average'] = '';
	$wpws_md_array['bse']['traded_quantity'] = '';
	$wpws_md_array['bse']['turnover'] = '';	
	
	/* More definition arrays to be defined here */
	
	if($return == 'url') {return $wpws_md_array[strtolower($market)]['url'];}
	elseif($return == 'selector') {return $wpws_md_array[strtolower($market)][strtolower($datatype)];}
}

?>