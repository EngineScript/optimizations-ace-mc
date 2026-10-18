<?php
/**
 * WooCommerce optimizations.
 *
 * @package OptimizationsAceMc
 * @since   1.0.9
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * WooCommerce optimizations.
 *
 * @since 1.0.9
 */
final readonly class Optimizations_Ace_Mc_WooCommerce_Optimizations {

	/**
	 * Constructor.
	 *
	 * @since 1.0.9
	 * @param Optimizations_Ace_Mc_Settings $settings Settings repository.
	 */
	public function __construct( private Optimizations_Ace_Mc_Settings $settings ) {
	}

	/**
	 * Register WooCommerce hooks.
	 *
	 * @since 1.0.9
	 */
	public function register_hooks(): void {
		if ( $this->settings->is_enabled( 'woocommerce_show_empty_categories' ) ) {
			add_filter( 'woocommerce_product_subcategories_hide_empty', '__return_false' );
		}

		if ( $this->settings->is_enabled( 'woocommerce_hide_category_count' ) ) {
			add_filter( 'woocommerce_subcategory_count_html', '__return_false' );
		}

		// The column calls WooCommerce directly, so skip it if WooCommerce is not loaded.
		if ( $this->settings->is_enabled( 'woocommerce_user_order_count_column' ) && is_admin() && function_exists( 'wc_get_customer_order_count' ) ) {
			add_filter( 'manage_users_columns', [ $this, 'add_user_order_count_column' ] );
			add_filter( 'manage_users_custom_column', [ $this, 'display_user_order_count_column' ], 10, 3 );
		}
	}

	/**
	 * Add order count column to users table.
	 *
	 * Other plugins share this filter, so a non-array value is passed through unchanged.
	 *
	 * @since 1.0.9
	 * @param mixed $columns Existing columns.
	 * @return mixed Modified columns.
	 * @psalm-api
	 */
	public function add_user_order_count_column( mixed $columns ): mixed {
		if ( ! is_array( $columns ) ) {
			return $columns;
		}

		$columns['user_order_count'] = __( 'Order Count', 'optimizations-ace-mc' );
		return $columns;
	}

	/**
	 * Display order count in users table.
	 *
	 * @since 1.0.9
	 * @param mixed  $output Custom column output from earlier callbacks.
	 * @param string $column_name Name of the column.
	 * @param int    $user_id User ID.
	 * @psalm-api
	 */
	public function display_user_order_count_column( mixed $output, string $column_name, int $user_id ): mixed {
		if ( 'user_order_count' !== $column_name ) {
			return $output;
		}

		$order_count = wc_get_customer_order_count( $user_id );

		return esc_html( number_format_i18n( $order_count ) );
	}
}
