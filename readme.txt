=== WP Web Scraper ===
Contributors: akshay_raje, WisdmLabs
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=akshay.raje@gmail.com&item_name=Donation+for+WP+Web+Scraper
Tags: web scraping, curl, css selector, xpath, regex, realtime, post, sidebar, page, stock market, html, import
Requires at least: 2.8
Tested up to: 4.1
Stable tag: 3.4

An easy to implement web scraper for WordPress. Display realtime data from any websites directly into your posts, pages or sidebar.

== Description ==

An easy to implement web scraper for WordPress. This can be used to display realtime data from any websites directly into your posts, pages or sidebar. Use this to include realtime stock quotes, cricket or soccer scores or any other generic content. Features include:

1. Scrap output can be displayed thru custom template tag, shortcode in page, post and sidebar (through a text widget).
1. Configurable caching of scraped data. Cache timeout in minutes can be defined in minutes for every scrap.
1. Configurable Useragent for your scraper can be set for every scrap.
1. Configurable default settings like enabling, useragent, timeout, caching, error handling.
1. [Multiple ways to query content](http://wp-ws.net/docs/query/) - CSS Selector, XPath or Regex.
1. A wide range of [arguments for parsing](http://wp-ws.net/docs/arguments-api/) content.
1. Option to pass post arguments to a URL to be scraped.
1. Dynamic conversion of scrap to specified character encoding to scrap data from a site using different charset.
1. Create scrap pages on the fly using [dynamic generation of URLs](http://wp-ws.net/docs/dynamic-url-headers/) to scrap or post arguments based on your page's get or post arguments.
1. [Callback function](http://wp-ws.net/docs/callback-functions/) for advanced parsing of scraped data.

Check the the official website [wp-ws.net](http://wp-ws.net/) for [documentation](http://wp-ws.net/docs/), browse through [examples](http://wp-ws.net/examples/), or try [paid support](http://wp-ws.net/support/) for crafting a perfectly optimized web scrape.

== Installation ==

1. Upload folder `wp-web-scrapper` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. [Usage instructions for WP Web Scraper](http://wp-ws.net/faqs/how-to-use-wp-web-scraper/)

Mode details on this on the [FAQs](http://wp-ws.net/faqs/) page

== Frequently Asked Questions ==

* [What is web scraping? Why do I need it in WordPress?](http://wp-ws.net/faqs/what-is-web-scraping-needed-in-wordpress/)
* [How to use WP Web Scraper?](http://wp-ws.net/faqs/how-to-use-wp-web-scraper/)
* [How to optimize performance?](http://wp-ws.net/faqs/how-to-optimize-performance/)
* [Minimum requirements & dependencies](http://wp-ws.net/faqs/minimum-requirements-dependencies/)
* [Posting external content as Posts or Pages](http://wp-ws.net/faqs/posting-external-content-as-posts-or-pages/)

== Documentation ==

* [Arguments API](http://wp-ws.net/docs/arguments-api/)
* [Query - CSS Selectors, XPath and Regex](http://wp-ws.net/docs/query/)
* [Dynamic URL and headers](http://wp-ws.net/docs/dynamic-url-headers/)
* [Callback Functions](http://wp-ws.net/docs/callback-functions/)

== Examples ==

[Example code](http://wp-ws.net/examples/) for some common use cases of the plugin

== Changelog ==

= 3.4 =
* Added scrap importer
* replace_query and replace_with now accepted specially formatted array arguments

= 3.3 =
* Basehref bug fix

= 3.2 =
* Documentation website change

= 3.1 =
* Bug fix: Minor bug fixes.

= 3.0 =
* Enhancement: Complete code rewrite, uses PHP DOM directly for faster processing
* Enhancement: Sandbox to test and debug
* Deprecation: Dropped `removetags`
* Changes: Changes in arguments

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

= 3.4 =
* Added scrap importer and implemented arrays in replace_query and replace_with