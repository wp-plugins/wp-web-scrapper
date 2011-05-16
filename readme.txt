=== WP Web Scraper ===
Contributors: akshay_raje
Donate link: http://webdlabs.com/projects/donate/
Tags: web scraping, curl, phpquery, xpath, realtime, post, sidebar, page, stock market, html, import
Requires at least: 2.8
Tested up to: 3.1.2
Stable tag: trunk

An easy to implement web scraper for WordPress. Display realtime data from any websites directly into your posts, pages or sidebar.

== Description ==

An easy to implement professional web scraper for WordPress. This can be used to display realtime data from any websites directly into your posts, pages or sidebar. Use this to include realtime stock quotes, cricket or soccer scores or any other generic content. The scraper is an extension of WP_HTTP class for scraping and uses phpQuery or xpath for parsing HTML. Features include:

1. Can be easily implemented using the button in the post / page editor.
1. Configurable caching of scraped data. Cache timeout in minutes can be defined in minutes for every scrap.
1. Configurable Useragent for your scraper can be set for every scrap.
1. Scrap output can be displayed thru custom template tag, shortcode in page, post and sidebar (through a text widget).
1. Other configurable settings like timeout, disabling shortcode etc.
1. Error handling - Silent fail, error display, custom error message or display expired cache.
1. Clear or replace a regex pattern from the scrap before output.
1. Option to pass post arguments to a URL to be scraped.
1. Dynamic conversion of scrap to specified character encoding (using incov) to scrap data from a site using different charset.
1. Create scrap pages on the fly using dynamic generation of URLs to scrap or post arguments based on your page's get or post arguments.
1. Callback function to parse the scraped data.

For demos and support, visit the [WP Web Scraper project page](http://webdlabs.com/projects/wp-web-scraper/). Comments appreciated.

== Installation ==

1. Upload folder `wp-web-scrapper` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the template tag `<?php echo wpws_get_content($url, $selector, $xpath, $wpwsopt)?>` in your template or the shortcode `[wpws url="" selector=""]` in your posts, pages and sidebar.

Mode details on this on the [FAQs](http://wordpress.org/extend/plugins/wp-web-scrapper/faq/) page

== Frequently Asked Questions ==

= What is web scraping? Why do I need it? =

Web scraping (or Web harvesting, Web data extraction) is a computer software technique of extracting information from websites. Web scraping focuses more on the transformation of unstructured Web content, typically in HTML format, into structured data that can be formatted and displayed or stored and analyzed. Web scraping is also related to Web automation, which simulates human Web browsing using computer software. Exemplary uses of Web scraping include online price comparison, weather data monitoring, market data tracking, Web content mashup and Web data integration.

= Sounds interesting, but how do I actually use it? =

Use the 'Add new web scrap' button to add a web scrap to your post or page. You can also use the template tag or shortcode detailed below.

WP Web Scraper can be used through a template tag (for direct integration in your theme) or shortcode (for posts, pages or sidebar) for scraping and displaying web content. Here's the actual usage detail:

For use within themes: `<?php echo wpws_get_content($url, $selector, $xpath, $wpwsopt)?>`

Example usage in theme:	`<?php echo wpws_get_content('http://google.com','title','','user_agent=My Bot&on_error=error_show&')?>` (Display the title tag of google's home page, using My Bot as a user agent)

For use directly in posts, pages or sidebar (text widget): `[wpws url="" selector=""]`

Example usage as a shortcode: `[wpws url="http://google.com" selector="title" user_agent="My Bot" on_error="error_show"]` (Display the title tag of google's home page, using My Bot as a user agent)

For usage of other advanced parameters refer the [Usage Manual](http://wordpress.org/extend/plugins/wp-web-scrapper/other_notes/)

Further details about selector syntax in [Selectors](http://wordpress.org/extend/plugins/wp-web-scrapper/other_notes/)

= Wow! I can actually create a complete meshup using this! =

Yes you can. However, you should consider the copyright of the content owner. Its best to at least attribute the content owner by a linkback or better take a written permission. Apart from rights, scraping in general is a very resource intensive task. It will exhaust the bandwidth of your host as well as the host of of the content owner. Best is not to overdo it. Ideally find single pages with enough content to create your your meshup.

= Okie. Then whats the best way to optimize its usage? =

Here are some tips to help you optimize the usage:

1. Keep the timeout as low as possible (least is 1 second). Higher timeout might impact your page processing time if you are dealing with content on slow servers.
1. If you plan use multiple scrapers in a single page, make sure you set the cache timeout to a larger period. Possibly as long as a day (i.e. 1440 minutes) or even more. This will cache content on your server and reduce scraping.
1. Use fast loading pages as your content source. Also prefer pages low in size to optimize performance.
1. Keep a close watch on your scraper. If the website changes its page layout, your selector may fail to fetch the right content.
1. If you are scraping a lot, keep a watch on your cache size too. Clear cache occasionaly.

= What libraries are used? What are the minimum requirements apart from WordPress =

For scraping, the plugin primarily uses [WP_HTTP classes](http://codex.wordpress.org/HTTP_API). For caching it uses the [Transients API](http://codex.wordpress.org/Transients_API). For parsing htm using CSS style selectors the plugin uses [phpQuery](http://code.google.com/p/phpquery/) - a server-side, chainable, CSS3 selector driven Document Object Model (DOM) API based on jQuery JavaScript Library and for xpath parsing it uses [JS_Extractor](http://jacksleight.com/old/code).

== Usage Manual ==

For use within themes: `<?php echo wpws_get_content($url, $selector, $xpath, $wpwsopt)?>` (selector or xpath is optional - you may use either of these)
Example usage in theme:	`<?php echo wpws_get_content('http://google.com','title','','user_agent=Bot+at+mysite.com&on_error=error_show&')?>` (Display the title tag of google's home page, using My Bot as a user agent)

For use directly in posts, pages or sidebar (text widget): `[wpws url="" selector=""]`
Example usage as a shortcode: `[wpws url="http://google.com" selector="title" user_agent="Bot at mysite.com" on_error="error_show"]` (Display the title tag of google's home page, using My Bot as a user agent)

Other supported arguments (for theme tag / shortcode) are as mentioned below. Only `url` and `selector` are required. All the rest are optional:

* url (Required): The complete URL which needs to be scraped.
* selector (Required): The jQuery style selector string to select the content to be scraped. You can use elements, ids or classes for this. Further details about selector syntax in 'Selectors' section below
* xpath: Generic xpath query can be used as an alternate query method over selectors.
* postargs: A string of post arguments to the page you are trying to scrap. For example `id=197&cat=5`
* clear_regex: Regex pattern to be cleared before the scraper flushes its output. For example `/[aeiou]/` will clear all single lowercase vowel from the output. This [Regex reference](http://gnosis.cx/publish/programming/regular_expressions.html) will be helpful.
* clear_selector: Similar to `clear_regex` but you can specify a CSS selector instead of regex.
* replace_regex: Regex pattern to be replaced with `replace_text` before the scraper flushes its output. For example `/[aeiou]/` will replace all single lowercase vowel from the output. This [Regex reference](http://gnosis.cx/publish/programming/regular_expressions.html) will be helpful.
* replace_selector: Similar to `replace_regex` but you can specify a CSS selector instead of regex.
* replace_with: String which will replace the regex pattern specified in `replace_text`.
* replace_selector_with: String which will replace the selector specified in `replace_selector`.
* basehref: A parameter which can be used to convert relative links from the scrap to absolute links. For example, `basehref="http://yahoo.com"`, will convert all relative links to absolute by appending `http://yahoo.com` to all href and scr values. Note that basehref needs to be complete path (with http) and no trailing slash.
* cache: Timeout interval of the cached data in minutes. If ignored, the default value specified in plugin settings will be used.
* output: Format of output rendered by the selector (text or html). Text format strips all html tags and returns only text content. Html format retirns the scrap as in with the html tags. If ignored, the default value 'text' will be used.
* user_agent: The USERAGENT header for cURL or Fopen. This string acts as your footprint while scraping data. If ignored, the default value specified in plugin settings will be used.
* timeout: Timeout interver for cURL or Fopen function in seconds. Higer the better for scraping slow servers, but this will also increase your page load time. Ideally should not exceed 2. If ignored, the default value specified in plugin settings will be used.
* on_error: Error handling options for cURL or Fopen. Available options are error_show (to display the error), error_hide (to fail silently) or error_show_cache (to display data from expired cache if any). Setting it to any other string will output the string itself. For instance `on_error="screwed!"` will output 'screwed!' if something goes wrong in the scrap. If ignored, the default value specified in plugin settings will be used.
* htmldecode: Specify a charset for `iconv` charset conversion of scraped content. You should specify the charset of the source url you are scraping from. If ignored, the default encoding of your blog will be used.
* striptags: Specify one or more tags in the format `<a><p>` to be striped off. Only the text content within these tags will be displayed. This can be used to strip off all links etc. If ignored, no tags are striped.
* removetags: Specify one or more tags in the format `<a><p>` to be removed. These tags and content within them will be removed. If ignored, no tags are removed.
* callback: Specify a function name which will parse the scrap as desired. Raw scrap should be an argument to the callback function and this function should return the desired processed output. Function can reside in functions.php of your theme too.
* debug: Set to 1 to turn on debug information in form of an html comment in scrap or set 0 to turn it off. Default value is 1.
* urldecode (only availabe in shortcode): Set to 1 to use `urldecode` for URLs with special characters. Set to 0 if you do not want to use it. Default value is 1.
* xpathdecode (only availabe in shortcode): Set to 1 to use `xpathdecode` for xpath queries with special characters. Set to 0 if you do not want to use it. Default value is 0.

== Selectors ==

This section specifically details usage of selectors which are the heart of WP Web Scraper. For parsing html, the plugin uses [phpQuery](http://code.google.com/p/phpquery/) and hence an elaborate documentation on selectors can be found at [phpQuery - Selector Documentation](http://code.google.com/p/phpquery/wiki/Selectors).

Frankly, selectors are a standard way to query the DOM structure of the scraped html document. phpQuery uses CSS selectors (like jQuery) and hence those familiar with CSS selectors will find themselves at home. To get you started, you can use elements, #ids, .classes to identify content. Here are a few examples:

* 'td .specialhead:eq(0)' will get you content within the first `<td>` on the page with a class 'specialhead'.
* 'table:eq(3) td:eq(3)' will get you content within the fourth `<td>` of the fourth `<table>` within the page.
* '#header div:eq(1)' will get you content within the second `<div>` inside the first element with id 'header'.

Since version 2.3, you can also optionally use xpaths to query your content. Details on usage of xpath can be found in the [PHP documentation](http://php.net/manual/en/simplexmlelement.xpath.php). XPaths can be handy while trying to scrape non-standard html tags or while working with RSS / ATOM or generic XML feeds.

== Dynamic URLs and postargs ==

At times you may have to create scraping paged on the fly to fetch content from a single underlying source by passing multiple get (page) arguments to it. For this, you may use an inbuilt feature which will convert specific text mentioned in url or postargs of your scrap to its corresponding value based on some get arguments specified on that page.

For example, if you want a page to scrap symbols on reuters.com/finance dynamically based on user input then:

* url should be `http://www.reuters.com/finance/stocks/overview?symbol=___symbol___`
* get argument for page should be `http://yourdomain.com/page/?symbol=CSCO.O (to get Cisco details)

This will replace `___symbol___` in the url with `CSCO.O` in realtime. You can use multiple such replacement variables in your url or postargs. Such replacement variables should be wrapped between 3 underscores. Note that field names being passed this was are case-sensitive. Having 'FieldName' vs. 'fieldname' makes a difference.

You can also use the special variable `___QUERY_STRING___` to replace the complete query string post ?

== Callback ==

Using the callback function, you can extend the plugin to do some advanced parsing. Simply put, its a function which will parse and return your data. Your callback function can reside in functions.php of your theme. The function should take a single string parameter, parse it and return a string as output.

== Changelog ==

= 2.8 =
* Enhancement: Migrated caching to the Transients API.
* Enhancement: Clear and find / replace now supports selectors.
* Enhancement: Cleaner code - faster processing.
* Enhancement: More debugging data including processing time.
* Deprecation: Modules are deprecated in support of callback functions.

= 2.7 =
* Enhancement: Added `callback` for flexible as well as advanced parsing.
* Bug fix: Fixed the issue of usage within widget.

= 2.6 =
* Enhancement: Added `removetags` to remove certain tags and content from scrap.
* Bug fix: Retains http-cache and modules on upgrade.

= 2.5 =
* Bug fix: Patched a major security issue related to useragent string settings.

= 2.4 =
* Bug fix: Added xpathdecode to handle complex xpath queries in shortcode.

= 2.3 =
* Enhancement: Added support for xpaths.
* Enhancement: Uses builtin WP_HTTP classes instead of raw cURL or Fopen.
* Enhancement: Complete overhaul of code, architecture and documentation.
* Enhancement: Reversed to filebased cache instead of MySQL tables.

= 2.2 =
* Enhancement: Introduction of special variable `___QUERY_STRING___` for dynamic URLs.
* Enhancement: Upgraded the underlying phpQuery library to single file version.

= 2.1 =
* Enhancement: Option to turn off the debug information displayed as html comment.

= 2.0 =
* Milestone release: Complete overhaul of code, architecture and documentation.
* Bug fix: Multiple bug fixes addressed.

= 1.8 =
* Bug fix: `htmldecode` now uses iconv to convert scrap to requested character encoding. No need to change the charset of your blog to render scrap properly.
* Enhancement: Added `striptags` to strip off non-required html tags from the scrap.

= 1.7 =
* Enhancement: Added `htmldecode` and `urldecode` to control htmlencoding and urlencoding in your scrap.

= 1.6 =
* Enhancement: Handling charecterset and usage of html entity decode.

= 1.5 =
* Bug fix: Charecter encoding bug fix

= 1.4 =
* Enhancement: Can now accept post arguments thru the shortcode `postargs`
* Enhancement: `url` and `postargs` can take one get or post variables each in shortcode. These variables should be written in the format `___varname___`

= 1.3 =
* Bug fix: Fixed a bug in the module `wpws_market_data`

= 1.2 =
* Bug fix: Can also accept urls with special charecters such as `[` or `]`. Such charecters need to be replaced by the equivalent URL-encoded string like `%5B` for `[` or `%5D` for `[` etc.

= 1.1 =
* Enhancement: Web scraps can now be added using a button in the post / page editor. No more remembering shortcodes.

= 1.0 =
* Enhancement: Added `basehref` parameter which can be used to convert relative links from the scrap to absolute links.
* Bug fix: Display of WP Web Scraper options page.

= 0.9 =
* Enhancement: Added `replace` and `replace_text` parameters to the wpws shortcode and tag. Its a regex pattern to be replaced with string specified in `replace_text` before the scraper flushes its output.

= 0.8 =
* Bug fix: `curl_setopt()` errors are now silent. No warnings displayed if things go wrong.

= 0.7 =
* Enhancement: BSE is also supported by `wpws_market_data`.
* Enhancement: Checked version compatibility with WP 2.8

= 0.6 =
* Bug fix: `wpws_market_data market` returned a debug text. Now fixed.

= 0.5 =
* Enhancement: Introduced a module architecture to develop custom mods or plugin extensions for common scraping tasks.
* Enhancement: Added the first mod `wpws_market_data` with support for NSE and NASDAQ exchanges.

= 0.4 =
* Bug fix: Multiple scraps from a single page now are cached as a single file. No multiple scraping for this.
* Enhancement: Added an option to display expired cache if scrap fails. Will display stale data instead of no data.

= 0.3 =
* Enhancement: Added clear parameter to the wpws shortcode and tag. Its a regex pattern to be cleared before the scraper flushes its output.
* Enhancement: Better error handling. Errors can now display actual error, fail silently or display your custom error.

= 0.2 =
* Bug fix: Display of WP Web Scraper options page.
* Bug fix: Calculation of files and size of cache.

== Upgrade Notice ==

= 2.8 =
* Enhancements: Migrated caching to the Transients API; Clear and find / replace now supports selectors; Cleaner code - faster processing; More debugging data including processing time and Modules are deprecated in support of callback functions.