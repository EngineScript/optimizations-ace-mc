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
final class Optimizations_Ace_Mc_WooCommerce_Optimizations {

	/**
	 * Settings repository.
	 *
	 * @since 1.0.9
	 * @var Optimizations_Ace_Mc_Settings
	 */
	private Optimizations_Ace_Mc_Settings $settings;

	/**
	 * Constructor.
	 *
	 * @since 1.0.9
	 * @param Optimizations_Ace_Mc_Settings $settings Settings repository.
	 */
	public function __construct( Optimizations_Ace_Mc_Settings $settings ) {
		$this->settings = $settings;
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

		if ( $this->settings->is_enabled( 'woocommerce_user_order_count_column' ) && is_admin() ) {
			add_filter( 'manage_users_columns', array( $this, 'add_user_order_count_column' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'display_user_order_count_column' ), 10, 3 );
		}
	}

	/**
	 * Add order count column to users table.
	 *
	 * @since 1.0.9
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string> Modified columns.
	 */
	public function add_user_order_count_column( array $columns ): array {
		$columns['user_order_count'] = __( 'Order Count', 'optimizations-ace-mc' );
		return $columns;
	}

	/**
	 * Display order count in users table.
	 *
	 * @since 1.0.9
	 * @param string $output Custom column output.
	 * @param string $column_name Name of the column.
	 * @param int    $user_id User ID.
	 * @return string
	 */
	public function display_user_order_count_column( string $output, string $column_name, int $user_id ): string {
		if ( 'user_order_count' !== $column_name ) {
			return $output;
		}

		$order_count = wc_get_customer_order_count( absint( $user_id ) );

		return esc_html( number_format_i18n( $order_count ) );
	}
}
