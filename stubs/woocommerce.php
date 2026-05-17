<?php
/**
 * Third-party plugin symbols used by static analysis.
 *
 * @package OptimizationsAceMc
 */

if ( ! defined( 'OPTIMIZATIONS_ACE_MC_PLUGIN_URL' ) ) {
	define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_URL', '' );
}

if ( ! defined( 'OPTIMIZATIONS_ACE_MC_PLUGIN_DIR' ) ) {
	define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_DIR', dirname( __DIR__ ) . DIRECTORY_SEPARATOR );
}

if ( ! defined( 'OPTIMIZATIONS_ACE_MC_VERSION' ) ) {
	define( 'OPTIMIZATIONS_ACE_MC_VERSION', '1.0.8' );
}

/**
 * Get a WooCommerce customer's order count.
 *
 * @param int $user_id User ID.
 * @return int
 */
function wc_get_customer_order_count( $user_id ) {
}

/**
 * Get the WP Store Locator info-window store header template.
 *
 * @return string
 */
function wpsl_store_header_template() {
}

/**
 * Get the WP Store Locator address placeholder template.
 *
 * @return string
 */
function wpsl_address_format_placeholders() {
}
