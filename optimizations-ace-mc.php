<?php
/**
 * Plugin Name: Optimizations ACE MC
 * Plugin URI: https://github.com/EngineScript/optimizations-ace-mc
 * Description: A lightweight WordPress optimization plugin with configurable performance enhancements for WooCommerce, WP Store Locator, and WordPress admin.
 * Version: 1.5.0
 * Author: EngineScript
 * Author URI: https://github.com/EngineScript
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: optimizations-ace-mc
 * Domain Path: /languages
 * Requires at least: 6.8
 * Tested up to: 6.9
 * Requires PHP: 8.2
 *
 * @package OptimizationsAceMc
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'OPTIMIZATIONS_ACE_MC_VERSION', '1.5.0' );
define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_FILE', __FILE__ );
define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/includes/class-optimizations-ace-mc-settings.php';
require_once __DIR__ . '/includes/class-optimizations-ace-mc-admin-page.php';
require_once __DIR__ . '/includes/class-optimizations-ace-mc-woocommerce-optimizations.php';
require_once __DIR__ . '/includes/class-optimizations-ace-mc-wpsl-optimizations.php';
require_once __DIR__ . '/includes/class-optimizations-ace-mc-admin-optimizations.php';
require_once __DIR__ . '/includes/class-optimizations-ace-mc.php';

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 * @return Optimizations_Ace_Mc
 */
function optimizations_ace_mc(): Optimizations_Ace_Mc {
	return Optimizations_Ace_Mc::instance();
}

/**
 * Load the plugin on the WordPress plugins_loaded hook.
 *
 * @since 1.0.9
 */
function optimizations_ace_mc_init(): void {
	optimizations_ace_mc()->register_hooks();
}

// Start the plugin once all other plugins are loaded.
add_action( 'plugins_loaded', 'optimizations_ace_mc_init' );
