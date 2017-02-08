=== Fast Search powered by Solr ===
Contributors: edwardbock katharinarompf
Tags: search, solr
Requires at least: 4.0
Tested up to: 4.4.2
Stable tag: 0.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Solr integration for your website. Replaces wordpress search query with solr apache core.

== Description ==

Solr integration for your website. Replaces wordpress search query with solr apache core.

For backwards compatibility, if this section is missing, the full length of the short description will be used, and
Markdown parsed.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/solr` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Solr screen to configure the plugin
1. Copy the `/wp-content/plugins/solr/templates/*` files to your '/wp-content/themes/theme/solr/*' directory to overwrite the templates


== Frequently Asked Questions ==

= How do I configure my Solr Core? =

You can use our config examples in the plugins 'solr-config' folder.

= How can I overwrite search templates? =

Create a 'solr' folder in your theme to overwrite templates. You can copy the templates in the plugin directory in 'templates'.

== Screenshots ==


== Changelog ==

= 0.3.3 =
* added scheduled events for indexing

= 0.3.2 =
* Name changed

= 0.3.1 =
* Added library ...

= 0.3 =
* First release