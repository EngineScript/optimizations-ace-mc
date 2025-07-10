# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial plugin structure
- Basic WordPress optimization framework
- Support for WordPress 6.5+
- Support for PHP 7.4+
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

### Changed
- Updated plugin to use WordPress 6.8 compatibility
- Fixed text domain to match plugin slug format
- Improved singleton pattern implementation
- Updated PHPMD configuration for WordPress coding standards
- Standardized text domain to 'optimizations-ace-mc' (lowercase, hyphenated) throughout codebase

### Fixed
- Text domain mismatch (now uses 'optimizations-ace-mc' consistently)
- PHPStan type checking issues with singleton pattern
- Removed invalid 'Network' header from plugin file
- WordPress compatibility testing up to version 6.8
- PHPMD warnings for WordPress naming conventions
- **Security improvements:**
  - Added proper capability checks for admin modifications
  - Added WooCommerce and WP Store Locator dependency checks
  - Proper data sanitization and escaping for all output
  - Fixed function name collision in user column sorting
  - Added input validation with `absint()` for user IDs

### Security
- All user inputs are properly sanitized and validated
- Capability checks ensure only authorized users can access admin features
- Plugin dependencies are verified before executing related functionality
- All output is properly escaped to prevent XSS attacks
