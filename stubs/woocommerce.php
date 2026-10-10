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
 * Drop WP Store Locator's cached store data (WP Store Locator 3.0 and later).
 *
 * @return void
 */
function wpsl_flush_store_cache(): void {
}
