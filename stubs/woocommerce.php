<?php
/**
 * Third-party plugin symbols used by static analysis.
 *
 * @package OptimizationsAceMc
 */

/**
 * Get a WooCommerce customer's order count.
 *
 * @param int $user_id User ID.
 * @return int
 */
function wc_get_customer_order_count( int $user_id ): int {
	return 0;
}

/**
 * Get the WP Store Locator info-window store header template.
 *
 * @return string
 */
function wpsl_store_header_template(): string {
	return '';
}

/**
 * Get the WP Store Locator address placeholder template.
 *
 * @return string
 */
function wpsl_address_format_placeholders(): string {
	return '';
}
