=== Optimizations ACE MC ===
Contributors: EngineScript
Tags: optimization, performance, wp-optimizer, speed, seo
Requires at least: 6.8
Tested up to: 6.9
Requires PHP: 8.2
Stable tag: 1.5.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A lightweight WordPress optimization plugin with configurable performance enhancements.

== Description ==

Optimizations ACE MC is a WordPress optimization plugin that provides configurable performance enhancements for WooCommerce, WP Store Locator, and WordPress admin interfaces.

= Features =

**WooCommerce Optimizations:**
* Show empty product categories in archives (configurable)
* Hide category product counts in product archives (configurable)
* Add a user order count column to the admin users table with sorting (configurable)

**WP Store Locator Optimizations:**
* Display store categories in store info windows (configurable)
* Disable the REST API for the store locator post type for enhanced security (configurable)

**WordPress Admin Optimizations:**
* Add a user registration date column to the admin users table with sorting (configurable)

**Settings Management:**
* Admin settings page for all optimizations
* Individual enable/disable controls for each feature
* User-friendly interface with clear descriptions

= Requirements =

* WordPress 6.8 or higher
* PHP 8.2 or higher
* WooCommerce 5.0+ (required - must be active)
* WP Store Locator (required - must be active)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/optimizations-ace-mc` directory, or install the plugin through the WordPress Plugins screen.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Navigate to **Settings > Optimizations ACE MC** to configure which optimization features you want to enable.
4. Enable or disable individual optimizations based on your site's needs and available plugins.

== Frequently Asked Questions ==

= Do I need to configure anything? =

All features are disabled by default. Navigate to **Settings > Optimizations ACE MC** to enable the optimizations you need. Each feature can be individually enabled or disabled.

= What happens if I don't have WooCommerce or WP Store Locator installed? =

WooCommerce and WP Store Locator are required dependencies for this plugin. Keep both plugins active before enabling their related optimization settings.

= Will this plugin slow down my site? =

No, this plugin is designed to improve performance by adding useful admin enhancements and optimizations. All features are optional and can be disabled if not needed.

= Is this plugin compatible with other optimization plugins? =

Yes, this plugin focuses on specific admin and functionality enhancements rather than general optimization, so it should work alongside other optimization plugins.

== Changelog ==

= 1.5.0 - 2026-05-18 =
* Changed: Updated the WordPress compatibility workflow matrix to test PHP 8.2 through 8.5 only
* Changed: Removed workflow matrix jobs below the minimum supported PHP version
* Changed: Raised minimum PHP version to 8.2 and minimum WordPress version to 6.8
* Changed: Added typed properties, parameter types, and return types to all class members (PHP 8.2+)
* Changed: Split the plugin PHP into focused classes under `includes/`
* Changed: Updated release and compatibility workflows to package and test `includes/`
* Changed: Extracted inline admin CSS to enqueued assets/css/admin.css stylesheet
* Changed: Made the store category label filterable via `apply_filters( 'optimizations_ace_mc_store_category_label' )`
* Changed: Cached the `date_format` option to avoid per-row database lookups
* Changed: Simplified store categories meta logic by removing separate single-term and multi-term branches
* Changed: Replaced manual settings-updated notice handling with WordPress `settings_errors()`
* Changed: Replaced manual registration date timestamp formatting with WordPress `get_date_from_gmt()`
* Removed: `display_dependencies_info()` method and inline dependency checks (plugins guaranteed active)
* Removed: `output_admin_styles()` method; replaced by external CSS file
* Removed: Unused `OPTIMIZATIONS_ACE_MC_PLUGIN_BASENAME` constant
* Fixed: Added `@since` tags to all methods missing them
* Fixed: Updated PHP version requirement text to "8.2+" in plugin info display
* Fixed: `.github/labeler.yml` references to nonexistent paths

= 1.0.8 - 2026-02-28 =
* Security: Hardened singleton pattern with private constructor and clone/wakeup prevention
* Security: Fixed missing input sanitization in settings update notification check
* Security: Added a `function_exists()` guard for `wc_get_customer_order_count()` to prevent fatal errors
* Fixed: Replaced incorrect sanitize_text_field() with proper esc_html() for store category output
* Fixed: Removed broken order count column sorting that had no query handler
* Fixed: Removed redundant capability checks in column render callbacks
* Fixed: Scoped admin CSS classes to prevent collisions with WordPress core and other plugins
* Fixed: FAQ now accurately states all features are disabled by default

= 1.0.7 - 2025-09-15 =
* Added: AI-powered code analysis workflow using Google Gemini for automated security scanning
* Added: Comprehensive WordPress coding standards compliance checking in CI/CD
* Added: Performance analysis for database queries and resource optimization
* Added: Pull request and push event analysis with detailed security feedback
* Enhanced: Dynamic workflow handling for both PR and push events with unified output
* Enhanced: Real-time code diff analysis with focus on security implications
* Security: Environment variable protection against command injection vulnerabilities
* Security: Secure API key management through GitHub repository secrets
* Fixed: Resolved 404 errors when posting PR comments on push events
* Fixed: JavaScript syntax errors and YAML parsing issues in workflow scripts
* Fixed: Improved workflow reliability and comprehensive error handling

= 1.0.6 - 2025-08-22 =
* Added: Comprehensive admin settings page for managing all plugin optimizations
* Added: User-configurable options for WooCommerce, WP Store Locator, and WordPress admin features
* Added: Individual enable/disable controls for each optimization feature
* Added: Plugin dependency status indicators with visual feedback
* Security: Fixed improper nonce verification in settings page form submission
* Security: Enhanced CSRF protection using proper WordPress Settings API handling
* Changed: All optimization features are now optional and user-configurable
* Changed: Features load conditionally based on user settings
* Fixed: Array alignment issues to meet WordPress coding standards
* Fixed: Removed unused variables and refactored long methods for better code quality
* Fixed: Converted entire codebase to WordPress-standard tab indentation
* Fixed: Updated PHPCS configuration to properly allow tabs for indentation per WordPress standards

= 1.0.5 - 2025-08-11 =
* Security: Fixed critical code injection vulnerabilities in GitHub Actions workflows
* Security: Implemented secure environment variable usage pattern for AI workflows
* Added: Comprehensive AI-powered workflow suite with Gemini integration
* Added: Automated code review and security scanning with AI assistance
* Added: Interactive Gemini assistant for code help and issue management
* Fixed: YAML corruption issues and duplicate environment variable definitions

= 1.0.4 - 2025-08-02 =
* Removed: All fallback methods and plugin availability checks for single-site deployment
* Removed: WooCommerce and WP Store Locator activation checks since plugins are guaranteed to be active
* Simplified: Direct function calls without existence validation for better performance
* Optimized: Code structure for dedicated single-site environment

= 1.0.3 - 2025-07-25 =
* Fixed: Code standards compliance - string concatenation issues
* Fixed: Variable alignment to follow WordPress coding standards
* Fixed: Line ending characters from CRLF to LF for consistency
* Fixed: PHPStan errors by updating function return types and class references
* Changed: Moved main class to proper WordPress naming convention file structure
* Added: Enhanced PHPStan configuration and development tools

= 1.0.2 - 2025-07-16 =
* Enhanced WordPress compatibility testing
* Improved automated code quality checks
* Added comprehensive CI/CD workflow
* Updated documentation and security guidelines

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
