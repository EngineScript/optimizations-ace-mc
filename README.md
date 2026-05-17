# Optimizations ACE MC

A lightweight WordPress optimization plugin with configurable performance enhancements for WooCommerce, WP Store Locator, and WordPress admin interfaces.

## Requirements

- WordPress 6.8+
- PHP 8.2+
- WooCommerce 5.0+
- WP Store Locator

## Features

- **WooCommerce**: Show empty categories, hide category product counts, and add a user order count column
- **WP Store Locator**: Display store categories in info windows and disable the REST API endpoint
- **WordPress Admin**: Add a sortable user registration date column

All features are individually configurable via **Settings > Optimizations ACE MC**.

## Structure

The main plugin file bootstraps focused classes from `includes/`:

- `Optimizations_Ace_Mc_Settings` handles option defaults and sanitization.
- `Optimizations_Ace_Mc_Admin_Page` handles the settings screen and assets.
- Dedicated optimization classes register WooCommerce, WP Store Locator, and WordPress admin hooks.

## Installation

1. Upload the plugin to `/wp-content/plugins/optimizations-ace-mc`.
2. Activate it from the WordPress Plugins screen.
3. Configure it at **Settings > Optimizations ACE MC**.

## License

GPL-3.0-or-later
