=== Sunny Search ===
Contributors: edwardbock katharinarompf
Tags: search, solr
Requires at least: 4.0
Tested up to: 4.8.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Solr integration for your website. Replaces wordpress search query with solr apache core.

== Description ==

Solr integration for your website. Replaces wordpress search query with solr apache core.

For backwards compatibility, if this section is missing, the full length of the short description will be used, and
Markdown parsed.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/sunny-search` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Solr screen to configure the plugin
1. Copy the `/wp-content/plugins/sunny-search/templates/*` files to your '/wp-content/themes/theme/plugin-parts/*' directory to overwrite the templates


== Frequently Asked Questions ==

= How do I configure my Solr Core? =

You can use our config examples in the plugins 'solr-config' folder.

= How can I overwrite search templates? =

Create a 'plugin-parts' folder in your theme to overwrite templates. You can copy the templates in the plugin directory.

== Screenshots ==


== Changelog ==

= 1.0 =
* Renaming
* Overwritten theme templates moved to plugin-parts folder

= 0.5.4 =
* Filter for autosuggest ajax solr query

= 0.5.3 =
* 404 state on search page 2+ fix

= 0.5.2 =
* Empty search bugfix
* Childtheme render parent theme templates fix
* Solr test search in backend

= 0.5.1 =
* Always use same search execution for same results

= 0.5 =
* Stable version
* JSON file config overwrites
* solr index flags moved to custom table

= 0.4.1 =
* the_content needs global $post context fix

= 0.3.3 =
* added scheduled events for indexing

= 0.3.2 =
* Name changed

= 0.3.1 =
* Added library ...

= 0.3 =
* First release

== Upgrade Notice ==

Please check your theme templates. With version 1.0 they moved from 'solr' to 'plugin-parts'