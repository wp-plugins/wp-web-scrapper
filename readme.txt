=== WP Web Scraper ===
Contributors: akshay_raje
Tags: web scraping, curl, phpquery, realtime, post, sidebar, page
Requires at least: 2.6
Tested up to: 2.7.1
Stable tag: 0.3

An easy to implement web scraper for WordPress. Display realtime data from any websites directly into your posts, pages or sidebar.

== Description ==

An easy to implement professional web scraper for WordPress. This can be used to display realtime data from any websites directly into your posts, pages or sidebar. Use this to include realtime stock quotes, cricket or soccer scores or any other generic content. The scraper is built using timetested libraries cURL for scraping and phpQuery for parsing HTML. Features include:

1. Configurable caching of scraped data. Cache timeout in minutes can be defined in minutes for every scrap.
1. Custom Useragent header for your scraper can be set for every scrap.
1. Scrap output can be displayed thru custom template tag, shortcode in page, post and sidebar (text widget).
1. Other configurable settings like cURL timeout, disabling shortcode etc.
1. Error handling - Silent fail, Error display or Custom error messages.
1. Option to clear a certain regex pattern from the scrap before output.

For demos and support, visit the [WP Web Scraper project page](http://webdlabs.com/projects/wp-web-scraper/). Comments appriciated.

**Get your scraper listed as an official example:** Using WP Web Scraper can be a bit tacky for first time users. Especially if you have not worked with jQuery style selectors before. To help such users, I intend to build a list of some working examples. If you have successfully implemented the scraper and dont mind sharing the magic, please drop the URL and selector string as a comment on the [WP Web Scraper project page](http://webdlabs.com/projects/wp-web-scraper/). I will list all such links in an 'examples' section.

== Installation ==

1. Upload folder `wp-web-scrapper` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the template tag `wpws_get_content($url, $selector)` in your template or the shortcode `[wpws url="" selector=""]` in your posts, pages and sidebar.

Mode details on this on the [FAQs](http://wordpress.org/extend/plugins/wp-web-scrapper/faq/) page

== Frequently Asked Questions ==

= What is web scraping? Why do I need it? =

Web scraping (or Web harvesting, Web data extraction) is a computer software technique of extracting information from websites. Web scraping focuses more on the transformation of unstructured Web content, typically in HTML format, into structured data that can be formatted and displayed or stored and analyzed. Web scraping is also related to Web automation, which simulates human Web browsing using computer software. Exemplary uses of Web scraping include online price comparison, weather data monitoring, market data tracking, Web content mashup and Web data integration.

= Sounds interesting, but how do I actually use it? =

WP Web Scraper plugin allows usage of a custom template tag (for template integration) or shortcode (for posts, pages or sidebar) for scraping and displaying web content. Here's the actual usage detail:

For use within themes: `<?php echo wpws_get_content($url, $selector, $clear, $cache_timeout, $output_format, $curl_agent, $curl_timeout, $error);?>`
	
For use directly in posts, pages or sidebar (text widget): `[wpws url="" selector="" clear ="" cache="" output="" agent="" timeout="" error=""]`

Arguments (for theme tag / shortcode) are:

* url / $url (Required): The complete URL which needs to be scraped.
* selector / $selector (Required): The jQuery style selector string to select the content to be scraped. You can use elements, ids or classes for this. Further details about selector syntax in [Selector Manual](http://wordpress.org/extend/plugins/wp-web-scrapper/other_notes/)
* clear / $clear: Regex pattern to be cleared before the scraper flushes its output. For example `/[aeiou]/` will clear all single lowercase vowel from the output. This [Regex reference](http://gnosis.cx/publish/programming/regular_expressions.html) will be helpful.
* cache / $cache_timeout: Timeout interval of the cached data in minutes. If ignored, the default value specified in plugin settings will be used.
* output / $output_format: Format of output rendered by the selector (text or html). Text format strips all html tags and returns only text content. Html format retirns the scrap as in with the html tags. If ignored, the default value 'text' will be used.
* agent / $curl_agent: The USERAGENT header for cURL. This string acts as your footprint while scraping data. If ignored, the default value specified in plugin settings will be used.
* timeout / $curl_timeout: Timeout interver for cURL function in seconds. Higer the better for scraping slow servers, but this will also increase your page load time. Ideally should not exceed 2. If ignored, the default value specified in plugin settings will be used.
* error / $error: Prints an error if cURL fails and if this param is set as 1. If it is set as 0, it silently fails. Setting it to any other string will output the string itself. For instance `error="screwed!"` will output 'screwed!' if something goes wrong in the scrap. This can be used for debugging. If ignored, the default value specified in plugin settings will be used.

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

Frankly, selectors are a standard way to query the DOM structure of the scraped html document. phpQuery uses jQuery-like selectors and hence those familier with jQuery selectors will find themselves at home. To get you started, you can use elements, #ids, .classes to identify content. Here are a few examples:

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