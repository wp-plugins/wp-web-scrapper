=== WP Web Scraper ===
Contributors: akshay_raje
Donate link: http://webdlabs.com/projects/donate/
Tags: web scraping, curl, phpquery, realtime, post, sidebar, page, stock market
Requires at least: 2.6
Tested up to: 2.8.4
Stable tag: 1.2

An easy to implement web scraper for WordPress. Display realtime data from any websites directly into your posts, pages or sidebar.

== Description ==

An easy to implement professional web scraper for WordPress. This can be used to display realtime data from any websites directly into your posts, pages or sidebar. Use this to include realtime stock quotes, cricket or soccer scores or any other generic content. The scraper is built using timetested libraries cURL for scraping and phpQuery for parsing HTML. Features include:

1. Can be easily implemented using the button in the post / page editor.
1. Configurable caching of scraped data. Cache timeout in minutes can be defined in minutes for every scrap.
1. Custom Useragent header for your scraper can be set for every scrap.
1. Scrap output can be displayed thru custom template tag, shortcode in page, post and sidebar (text widget).
1. Other configurable settings like cURL timeout, disabling shortcode etc.
1. Error handling - Silent fail, standard error, custom error message or display expired cache.
1. Option to clear or replace a certain regex pattern from the scrap before output.
1. Built-in module for stock market data (NSE, BSE and NASDAQ supported currently), other markets to follow. For example - `[wpws_market_data market="NSE" symbol="ACC" datatype="last"]` will output the latest price of ACC on NSE. Refer [FAQs](http://wordpress.org/extend/plugins/wp-web-scrapper/faq/) for more details of this shortcode API.

For demos and support, visit the [WP Web Scraper project page](http://webdlabs.com/projects/wp-web-scraper/). Comments appriciated.

**Looking for an easy to use API for free Stock Market feeds (RSS, JSON etc)?** Try out a prototype API which I am working on, visit [http://smdapi.co.cc](http://smdapi.co.cc) for further details.

== Installation ==

1. Upload folder `wp-web-scrapper` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the template tag `wpws_get_content($url, $selector)` in your template or the shortcode `[wpws url="" selector=""]` in your posts, pages and sidebar.

Mode details on this on the [FAQs](http://wordpress.org/extend/plugins/wp-web-scrapper/faq/) page

== Frequently Asked Questions ==

= What is web scraping? Why do I need it? =

Web scraping (or Web harvesting, Web data extraction) is a computer software technique of extracting information from websites. Web scraping focuses more on the transformation of unstructured Web content, typically in HTML format, into structured data that can be formatted and displayed or stored and analyzed. Web scraping is also related to Web automation, which simulates human Web browsing using computer software. Exemplary uses of Web scraping include online price comparison, weather data monitoring, market data tracking, Web content mashup and Web data integration.

= Sounds interesting, but how do I actually use it? =

Use the 'Add new web scrap' button to add a web scrap to your post or page. You can also use the template tag or shortcode detailed below.

WP Web Scraper plugin allows usage of a custom template tag (for template integration) or shortcode (for posts, pages or sidebar) for scraping and displaying web content. Here's the actual usage detail:

For use within themes: `<?php echo wpws_get_content($url, $selector, $clear, $cache_timeout, $output_format, $curl_agent, $curl_timeout, $error);?>`
	
For use directly in posts, pages or sidebar (text widget): `[wpws url="" selector="" clear="" cache="" output="" agent="" timeout="" error=""]`

Arguments (for theme tag / shortcode) are as mentioned below. Only `url` and `selector` are required. All the rest are optional:

* url / $url (Required): The complete URL which needs to be scraped.
* selector / $selector (Required): The jQuery style selector string to select the content to be scraped. You can use elements, ids or classes for this. Further details about selector syntax in [Selector Manual](http://wordpress.org/extend/plugins/wp-web-scrapper/other_notes/)
* clear / $clear: Regex pattern to be cleared before the scraper flushes its output. For example `/[aeiou]/` will clear all single lowercase vowel from the output. This [Regex reference](http://gnosis.cx/publish/programming/regular_expressions.html) will be helpful.
* replace / $replace: Regex pattern to be replaced with `replace_text` before the scraper flushes its output. For example `/[aeiou]/` will replace all single lowercase vowel from the output. This [Regex reference](http://gnosis.cx/publish/programming/regular_expressions.html) will be helpful.
* replace_text / $replace_text: String which will replace the regex pattern specified in `replace_text`.
* basehref / $basehref: A parameter which can be used to convert relative links from the scrap to absolute links. For example, `basehref="http://yahoo.com"`, will convert all relative links to absolute by appending `http://yahoo.com` to all href and scr values. Note that basehref needs to be complete path (with http) and no trailing slash.
* cache / $cache_timeout: Timeout interval of the cached data in minutes. If ignored, the default value specified in plugin settings will be used.
* output / $output_format: Format of output rendered by the selector (text or html). Text format strips all html tags and returns only text content. Html format retirns the scrap as in with the html tags. If ignored, the default value 'text' will be used.
* agent / $curl_agent: The USERAGENT header for cURL. This string acts as your footprint while scraping data. If ignored, the default value specified in plugin settings will be used.
* timeout / $curl_timeout: Timeout interver for cURL function in seconds. Higer the better for scraping slow servers, but this will also increase your page load time. Ideally should not exceed 2. If ignored, the default value specified in plugin settings will be used.
* error / $error: Prints an error if cURL fails and if this param is set as 1. If it is set as 0, it silently fails. If set to 'cache' it will display data from expired cache (if any). Setting it to any other string will output the string itself. For instance `error="screwed!"` will output 'screwed!' if something goes wrong in the scrap. If ignored, the default value specified in plugin settings will be used.

= That sounds complex. Any simple shortcodes? =

Yes, WP Web Scraper now also supports simple short codes for standard scraping tasks. Currently it only supports stock market data shortcode, however I am planning to also bring in stuff like weather, sports etc too.

Here is a usage example of the stock market data shortcode: `[wpws_market_data market="" symbol="" datatype=""]`

Arguments accepted are:

* market (Required): Stock market name. Currently supports only NSE, BSE and NASDAQ. (More coming soon)
* symbol (Required): Symbol for which the data is to be scraped. Make sure you specify the right sumbol code related to the market.
* datatype: What specific datatype you intend to scrap. Options available are: `name`, `52_week_high`, `52_week_low`, `open`, `high`, `low`, `last`, `previous_close`, `change_amount`, `change_percent`, `average`, `traded_quantity` and `turnover`. If ignored, `last` outputted.
* You may also use all other arguments taken by the shortcode `[wpws]` like clear, cache, output, agent, timeout and error to finetune / optimize your scrap.

For example,

* `[wpws_market_data market="NSE" symbol="ACC" datatype="last"]` will output the latest price of ACC on NSE.
* `[wpws_market_data market="NASDAQ" symbol="CSCO" datatype="high"]` will output the intraday high of Cisco on NASDAQ.

= Wow! I can actually create a complete meshup using this! =

Yes you can. However, you should consider the copyright of the content owner. Its best to at least attribute the content owner by a linkback or better take a written permission. Apart from rights, cURLing in general is a very resource intensive task. It will exhaust the bandwidth of your host as well as the host of of the content owner. Best is not to overdo it. Ideally find single pages with enough content to create your your meshup.

= Okie. Then whats the best way to optimise its usage? =

Here are some tips to help you optimise the usage:

1. Keep the timeout as low as possible (least is 1 second). Higher timeout might impact your page processing time if you are dealing with content on slow servers.
1. If you plan use multiple scrapers in a single page, make sure you set the cache timeout to a larger period. Possibly as long as a day (i.e. 1440 minutes) or even more. This will cache content on your server and reduce cURLing.
1. Use fast loading pages as your content source. Also prefer pages low in size to optimize performance.
1. Keep a close watch on your scraper. If the website changes its page layout, your selector may fail to fetch the right content.
1. If you are scraping a lot, keep a watch on your cache size too. Clear cache occasionaly.

= What libraries are used? What are the minimum requirements apart from WordPress =

For scraping, the plugin primarily uses [cURL](http://php.net/curl). This is a very robust library (libcurl) which comes embeded with PHPs pre compiled versions. Verify your php.ini or phpinfo() to check if your host supports this. For parsing html, the plugin uses [phpQuery](http://code.google.com/p/phpquery/). This is a server-side, chainable, CSS3 selector driven Document Object Model (DOM) API based on jQuery JavaScript Library.

== Selector Manual ==

This page will specifically detail usage of selectors which is the heart of WP Web Scraper. For parsing html, the plugin uses [phpQuery](http://code.google.com/p/phpquery/) and hence an elaborate documentation on selectors can be found at [phpQuery - Selector Documentation](http://code.google.com/p/phpquery/wiki/Selectors).

Frankly, selectors are a standard way to query the DOM structure of the scraped html document. phpQuery uses CSS selectors (like jQuery) and hence those familier with jQuery selectors will find themselves at home. To get you started, you can use elements, #ids, .classes to identify content. Here are a few examples:

* 'td .specialhead:eq(0)' will get you content within the first `<td>` on the page with a class 'specialhead'.
* 'table:eq(3) td:eq(3)' will get you content within the fourth `<td>` of the fourth `<table>` within the page.
* '#header div:eq(1)' will get you content within the second `<div>` inside the first element with id 'header'.

== Change Log ==

**Version 0.2**

1. Bug fix: Display of WP Web Scraper options page.
1. Bug fix: Calculation of files and size of cache.

**Version 0.3**

1. Enhancement: Added clear parameter to the wpws shortcode and tag. Its a regex pattern to be cleared before the scraper flushes its output.
1. Enhancement: Better error handling. Errors can now display actual error, fail silently or display your custom error.

**Version 0.4**

1. Bug fix: Multiple scraps from a single page now are cached as a single file. No multiple scraping for this.
1. Enhancement: Added an option to display expired cache if scrap fails. Will display stale data instead of no data.

**Version 0.5**

1. Enhancement: Introduced a module architecture to develop custom mods or plugin extensions for common scraping tasks.
1. Enhancement: Added the first mod `wpws_market_data` with support for NSE and NASDAQ exchanges.

**Version 0.6**

1. Bug fix: `wpws_market_data market` returned a debug text. Now fixed.

**Version 0.7**

1. Enhancement: BSE is also supported by `wpws_market_data`.
1. Enhancement: Checked version campatibility with WP 2.8

**Version 0.8**

1. Bug fix: `curl_setopt()` errors are now silent. No warnings displayed if things go wrong.

**Version 0.9**

1. Enhancement: Added `replace` and `replace_text` parameters to the wpws shortcode and tag. Its a regex pattern to be replaced with string specified in `replace_text` before the scraper flushes its output.

**Version 1.0**

1. Enhancement: Added `basehref` parameter which can be used to convert relative links from the scrap to absolute links.
1. Bug fix: Display of WP Web Scraper options page.

**Version 1.1**

1. Enhancement: Web scraps can now be added using a button in the post / page editor. No more remembering shortcodes.

**Version 1.2**

1. Bug fix: Can also accept urls with special charecters such as `[` or `]`. Such charecters need to be replaced by the equivalent URL-encoded string like `%5B` for `[` or `%5D` for `[` etc.
