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

### Changed
- Updated plugin to use WordPress 6.8 compatibility
- Fixed text domain to match plugin slug format
- Improved singleton pattern implementation
- Updated PHPMD configuration for WordPress coding standards

### Fixed
- Text domain mismatch (now uses 'Optimizations-ACE-MC')
- PHPStan type checking issues with singleton pattern
- Removed invalid 'Network' header from plugin file
- WordPress compatibility testing up to version 6.8
- PHPMD warnings for WordPress naming conventions

### Removed
- Unused admin interface files (keeping single-file structure)
- Invalid plugin headers
