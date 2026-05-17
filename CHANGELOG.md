# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- **CI/CD:**
  - Updated the WordPress compatibility workflow matrix to test PHP 8.2 through 8.5 only
  - Removed matrix jobs below the plugin minimum PHP requirement

- **PHP 8.2+ and WordPress 6.8+ Modernization:**
  - Raised the minimum PHP version to 8.2 and the minimum WordPress version to 6.8 across plugin metadata, docs, and tooling
  - Added typed properties (`?self`, `array`, `readonly array`, `string`) to all class properties
  - Added parameter type declarations and return type declarations to all methods
  - Replaced `isset()` ternary patterns with null coalescing operator
  - Used `readonly` modifier for immutable `$default_settings` property
  - Simplified `sanitize_settings()` with concise boolean casting

- **Performance:**
  - Cached `get_option( 'date_format' )` result in `$date_format` property to avoid per-row database lookups
  - Extracted inline admin CSS to enqueued `assets/css/admin.css` stylesheet, loaded only on plugin settings page
  - Removed `output_admin_styles()` method and inline `<style>` block

- **Architecture:**
  - Split the plugin from one large PHP class into focused classes under `includes/`
  - Updated release and compatibility workflows to package and test the `includes/` layout
  - Made store category label filterable via `apply_filters( 'optimizations_ace_mc_store_category_label' )`
  - Simplified `add_store_categories_to_meta()` by removing separate single-term and multi-term branches
  - Captured `add_submenu_page()` return value in `$settings_page_hook` property for targeted asset loading
  - Removed unused `OPTIMIZATIONS_ACE_MC_PLUGIN_BASENAME` constant
  - Replaced manual settings-updated notice handling with WordPress `settings_errors()`
  - Replaced manual registration date timestamp formatting with WordPress `get_date_from_gmt()`

### Removed

- Removed `display_dependencies_info()` method; WooCommerce and WP Store Locator are guaranteed active
- Removed `function_exists( 'wc_get_customer_order_count' )` guard; WooCommerce is guaranteed active
- Removed `class_exists()` / `function_exists()` dependency checks in display methods

### Fixed

- Added `@since` tags to all methods missing them
- Updated settings description for store categories to be generic (removed hard-coded "Certifications" mention)
- Updated PHP version text to "8.2+ required" in plugin info display
- Fixed `.github/labeler.yml` references to nonexistent paths (`templates/**/*`, `docs/**/*`, `.travis.yml`)

## [1.0.8] - 2026-02-28

### Security

- Hardened singleton pattern with private constructor and clone/wakeup prevention
- Fixed missing input sanitization in settings update notification check
- Added `function_exists()` guard for `wc_get_customer_order_count()` to prevent fatal errors if WooCommerce is deactivated

### Fixed

- Replaced incorrect `sanitize_text_field()` with proper `esc_html()` for store category term output context
- Removed broken order count column sorting (no query handler existed to support it)
- Removed redundant `is_admin()` and `current_user_can()` checks in column render callbacks already guarded at hook registration
- Scoped admin CSS classes to prevent collisions with WordPress core `.card` styles and other plugins
- Fixed FAQ to accurately state that all features are disabled by default

## [1.0.7] - 2025-09-15

### Added

- **CI/CD Automation:**
  - Implemented comprehensive AI-powered code analysis workflow using Google Gemini
  - Added automated security vulnerability scanning for all code changes
  - Integrated WordPress coding standards compliance checking
  - Added performance analysis for database queries and resource optimization
  - Added pull request and push event analysis with detailed feedback
  - Secure API key management through GitHub repository secrets

### Enhanced

- **Development Workflow:**
  - Dynamic workflow handling for both PR and push events
  - Unified output system that adapts based on event type
  - Real-time code diff analysis focused on security implications
  - Comprehensive error handling and fallback mechanisms
  - Official Google AI SDK integration replacing unofficial CLI tools

### Security

- **Workflow Security:**
  - Environment variable protection against command injection vulnerabilities
  - Secure handling of API credentials in GitHub Actions
  - Protected secret management for sensitive operations
  - Enhanced authentication verification for AI analysis services

### Fixed

- **GitHub Actions:**
  - Resolved 404 errors when posting PR comments on push events
  - Fixed JavaScript syntax errors in workflow scripts
  - Corrected YAML parsing issues with template literals
  - Improved workflow reliability and error reporting

## [1.0.6] - 2025-08-22

### Added

- **Settings Page:**
  - Comprehensive admin settings page for managing all plugin optimizations
  - User-configurable options for WooCommerce, WP Store Locator, and WordPress admin features
  - Individual enable/disable controls for each optimization feature
  - Plugin dependency status indicators with visual feedback
  - Responsive admin interface with proper WordPress styling
  - Full internationalization support for all settings text

### Security

- **Settings Security:**
  - Fixed improper nonce verification in settings page form submission
  - Implemented proper WordPress Settings API nonce handling
  - Enhanced CSRF protection by removing manual nonce checks in favor of WordPress core handling
  - Maintained proper capability checks (manage_options) for admin access
  - All form inputs sanitized using WordPress Settings API sanitization callbacks

### Changed

- **Plugin Architecture:**
  - All optimization features are now optional and user-configurable
  - Features load conditionally based on user settings instead of being always active
  - Improved plugin structure with centralized settings management
  - Enhanced user experience with clear feature descriptions and dependency information

### Fixed

- **Code Quality:**
  - Fixed array alignment issues to meet WordPress coding standards
  - Removed unused variable in sanitize_settings() method
  - Refactored long methods (init_settings, settings_page) into smaller, focused methods
  - Improved code maintainability and readability
  - Enhanced method separation for better testing and debugging
  - Converted entire codebase from space indentation to WordPress-standard tab indentation
  - Updated PHPCS configuration to allow tabs for indentation per WordPress standards

## [1.0.5] - 2025-08-11

### Security

- **GitHub Actions Security:**
  - Fixed critical code injection vulnerabilities in all AI-powered workflows
  - Implemented secure environment variable usage pattern to prevent script injection attacks
  - Applied two-stage security pattern for handling untrusted step outputs
  - Secured workflows: ai-pr-comment.yml, gemini-security-scan.yml, wordpress-standards-check.yml

### Added

- **AI-Powered Workflows:**
  - Comprehensive GitHub Actions workflow suite with Gemini AI integration
  - Automated code review and security scanning with AI assistance
  - Pull request analysis and automated commenting system
  - WordPress coding standards validation with AI recommendations
  - Gemini assistant for interactive code help and issue management

### Fixed

- **Workflow Integrity:**
  - Resolved YAML corruption issues in GitHub Actions workflows
  - Fixed duplicate environment variable definitions
  - Corrected workflow syntax and structure for proper execution

## [1.0.4] - 2025-08-02

### Removed

- **Plugin Checks:**
  - Removed WooCommerce activation check (`is_woocommerce_active()` method)
  - Removed WP Store Locator activation check (`is_wpsl_active()` method)
  - Removed `function_exists( 'wc_get_customer_order_count' )` fallback check

### Changed

- **Performance:**
  - Optimized for single-site deployment where WooCommerce and WP Store Locator are guaranteed to be active
  - Simplified code structure by removing unnecessary plugin availability validations
  - Direct function calls without existence checks for better performance

## [1.0.3] - 2025-07-25

### Fixed

- **Code Standards:**
  - Fixed string concatenation issues by removing unnecessary concatenation operators
  - Corrected variable alignment to follow WordPress coding standards
  - Converted line endings from CRLF to LF for consistency
  - Resolved PHPStan errors by updating function return types and class references
  - Added proper bootstrap configuration for PHPStan analysis

### Changed

- **File Structure:**
  - Moved main class to `class-optimizations-ace-mc.php` following WordPress naming conventions
  - Updated main plugin file to include class file properly
  - Improved code organization and maintainability

### Added

- **Development Tools:**
  - Added `.distignore` file for proper WordPress.org deployment
  - Enhanced PHPStan configuration with proper bootstrap files
  - Improved workflow compatibility testing

## [1.0.2] - 2025-07-16

### Fixed

- **Coding Standards:**
  - Corrected various PHPCS coding standards violations, including alignment and quote usage.
  - Fixed an XML syntax error in the `phpcs.xml` ruleset file.

## [1.0.1] - 2025-07-10

### Fixed

- **PHPStan compatibility:**
  - Added `function_exists()` checks for WP Store Locator functions to prevent errors when the plugin is not installed
  - Fixed `WP_Term` property access using `! empty()` instead of `isset()` to satisfy PHPStan analysis
  - Added PHPStan ignore rules for WP Store Locator functions in configuration
- **PHPMD configuration:**
  - Updated PHPMD ruleset to properly exclude WordPress naming conventions (snake_case)
  - Fixed camelCase naming rule conflicts with WordPress coding standards
- **Code quality improvements:**
  - Improved error handling for missing plugin dependencies
  - Enhanced static analysis compliance
  - Fixed text domain mismatch (now uses 'optimizations-ace-mc' consistently)
  - Fixed PHPStan type checking issues with singleton pattern
  - Removed invalid 'Network' header from plugin file
  - Fixed function name collision in user column sorting

### Changed

- Updated singleton pattern implementation to avoid PHPStan static access warnings
- Improved text domain consistency throughout the codebase
- Updated plugin to use WordPress 6.8 compatibility
- Fixed text domain to match plugin slug format
- Improved singleton pattern implementation
- Updated PHPMD configuration for WordPress coding standards
- Standardized text domain to 'optimizations-ace-mc' (lowercase, hyphenated) throughout codebase

### Added

- Initial plugin structure
- Basic WordPress optimization framework
- Support for WordPress 6.8+
- Support for PHP 8.2+
- Internationalization support
- Security checks and validation
- **WooCommerce optimizations:**
  - Show empty product categories in archives
  - Hide category product count in product archives
  - User order count column in admin users table (sortable)
- **WP Store Locator optimizations:**
  - Display store categories in store info windows
  - Disable REST API for store locator post type
  - Custom info window template with certifications
- **WordPress admin optimizations:**
  - User registration date column in admin users table (sortable)

### Security

- All user input is properly sanitized and validated
- Capability checks ensure only authorized users can access admin features
- Plugin dependencies are verified before executing related functionality
- All output is properly escaped to prevent XSS attacks
- Added proper capability checks for admin modifications
- Added WooCommerce and WP Store Locator dependency checks
- Added input validation with `absint()` for user IDs
