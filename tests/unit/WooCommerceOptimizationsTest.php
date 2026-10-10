<?php
/**
 * WooCommerce feature tests that run without WooCommerce.
 *
 * @package OptimizationsAceMc
 */

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for the category filters and for the order count column while
 * WooCommerce is not loaded. WooCommerceOrderCountTest covers the column with
 * WooCommerce present, in a separate process.
 */
final class WooCommerceOptimizationsTest extends Oam_Test_Case {

	/**
	 * Each category setting adds its own WooCommerce filter and no other.
	 *
	 * @param array<int, string> $enabled Settings that are on.
	 * @param array<int, string> $hooks   Hooks that must be added.
	 */
	#[DataProvider( 'provide_category_settings' )]
	public function test_category_settings_add_their_filters( array $enabled, array $hooks ): void {
		( new Optimizations_Ace_Mc_WooCommerce_Optimizations( $this->settings_enabled( ...$enabled ) ) )->register_hooks();

		$this->assertSame( $hooks, $this->hooked_names() );
		foreach ( $hooks as $hook ) {
			$this->assertSame( array( '__return_false' ), $this->callbacks_on( $hook ) );
			$this->assertFalse( apply_filters( $hook, true ) );
		}
	}

	/**
	 * Category settings and the filters they add.
	 *
	 * @return array<string, array{0: array<int, string>, 1: array<int, string>}>
	 */
	public static function provide_category_settings(): array {
		return array(
			'nothing on'         => array( array(), array() ),
			'show empty'         => array( array( 'woocommerce_show_empty_categories' ), array( 'woocommerce_product_subcategories_hide_empty' ) ),
			'hide count'         => array( array( 'woocommerce_hide_category_count' ), array( 'woocommerce_subcategory_count_html' ) ),
			'both'               => array(
				array( 'woocommerce_show_empty_categories', 'woocommerce_hide_category_count' ),
				array( 'woocommerce_product_subcategories_hide_empty', 'woocommerce_subcategory_count_html' ),
			),
			'unrelated settings' => array( array( 'wpsl_disable_rest_api', 'admin_user_registration_date_column' ), array() ),
		);
	}

	/**
	 * The category filters work on the front end too.
	 */
	public function test_category_filters_do_not_need_the_admin(): void {
		$GLOBALS['oam_test']['is_admin'] = false;

		( new Optimizations_Ace_Mc_WooCommerce_Optimizations( $this->settings_enabled( 'woocommerce_show_empty_categories', 'woocommerce_hide_category_count' ) ) )->register_hooks();

		$this->assertSame( array( 'woocommerce_product_subcategories_hide_empty', 'woocommerce_subcategory_count_html' ), $this->hooked_names() );
	}

	/**
	 * Without WooCommerce the order count column is not added, so that the Users screen cannot call a missing function.
	 */
	public function test_order_count_column_is_skipped_without_woocommerce(): void {
		$this->assertFalse( function_exists( 'wc_get_customer_order_count' ), 'WooCommerce must not be defined in this process.' );

		( new Optimizations_Ace_Mc_WooCommerce_Optimizations( $this->settings_enabled( 'woocommerce_user_order_count_column' ) ) )->register_hooks();

		$this->assertSame( array(), $this->hooked_names() );
	}

	/**
	 * The column is appended and the existing columns are kept.
	 */
	public function test_column_is_added_after_the_existing_columns(): void {
		$feature = new Optimizations_Ace_Mc_WooCommerce_Optimizations( new Optimizations_Ace_Mc_Settings() );

		$columns = $feature->add_user_order_count_column( array( 'username' => 'Username' ) );

		$this->assertSame( array( 'username', 'user_order_count' ), array_keys( $columns ) );
		$this->assertSame( 'Username', $columns['username'] );
		$this->assertNotSame( '', $columns['user_order_count'] );
	}

	/**
	 * A column list of the wrong type, and cells of other columns, are passed on unchanged.
	 */
	public function test_foreign_values_pass_through(): void {
		$feature = new Optimizations_Ace_Mc_WooCommerce_Optimizations( new Optimizations_Ace_Mc_Settings() );

		foreach ( array( null, false, 'columns', 7 ) as $foreign ) {
			$this->assertSame( $foreign, $feature->add_user_order_count_column( $foreign ) );
		}

		foreach ( array( '', '<b>kept</b>', null, false, 12 ) as $output ) {
			foreach ( array( 'registration_date', 'email', '', 'user_order_count ' ) as $column ) {
				$this->assertSame( $output, $feature->display_user_order_count_column( $output, $column, 5 ) );
			}
		}
		$this->assertSame( array(), $GLOBALS['oam_test']['number_format_calls'] );
	}
}
