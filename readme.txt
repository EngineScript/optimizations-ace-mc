=== Optimizations ACE MC ===
Contributors: enginescript
Tags: optimization, performance, wp-optimizer, speed, seo
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A lightweight WordPress optimization plugin with pre-configured performance enhancements.

== Description ==

Optimizations ACE MC is a simple, lightweight WordPress optimization plugin that runs pre-configured performance enhancements to improve your website's speed and efficiency.

= Features =

* Remove WordPress version from head
* Remove unnecessary meta tags
* Remove emoji scripts and styles
* Disable REST API for non-logged-in users
* Remove query strings from static resources

= Requirements =

* WordPress 6.5 or higher
* PHP 7.4 or higher

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/optimizations-ace-mc` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. The optimization features will be automatically enabled upon activation.

== Frequently Asked Questions ==

= Do I need to configure anything? =

No, all optimizations are automatically enabled when the plugin is activated. This plugin is designed to be simple and work out of the box.

= Will this plugin slow down my site? =

No, this plugin is designed to improve performance by removing unnecessary code and optimizations that can slow down your site.

= Is this plugin compatible with other optimization plugins? =

Yes, this plugin focuses on basic optimizations and should work alongside other optimization plugins.

== Changelog ==

= 1.0.1 =
* Fixed PHPStan compatibility issues with WP Store Locator functions
* Improved PHPMD configuration for WordPress coding standards
* Enhanced error handling for missing plugin dependencies
* Updated singleton pattern to avoid static analysis warnings
* Fixed WP_Term property access validation

= 1.0.0 =
* Initial release
* Core optimization functions
* WordPress performance enhancements
* Language support
