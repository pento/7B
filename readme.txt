=== 7B ===
Contributors: pento
Requires at least: 3.7
Tested up to: 3.8
Stable tag: 0.2
License: GPL2+

An experiment in the use of curly brackets.

== Description ==

Adds a JSON feed to your site. Highly experimental, expect it to break, change, etc. at any time.

There's a [ticket for bugs/feedback](http://core.trac.wordpress.org/ticket/25639).

== Installation ==

= The Old Way =

1. Upload the plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

= The Living-On-The-Edge Way =

(Please don't do this in production, you will almost certainly break something!)

1. Checkout the current development version from http://plugins.svn.wordpress.org/7B/trunk/
1. Subscribe to the [RSS feed](http://plugins.trac.wordpress.org/log/7B?limit=100&mode=stop_on_copy&format=rss) to be notified of changes

== Changelog ==

= 0.3 =
* ADDED: Pluggable JSON feeds support

= 0.2 =
* ADDED: Sanity checks for JSONP callback. Props @rmccue
* FIXED: json_encode() options not available in earlier PHP versions. Props @rmccue

= 0.1 =
* Initial release
