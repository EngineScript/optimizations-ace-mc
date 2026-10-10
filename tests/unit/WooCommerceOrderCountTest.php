<?php
/**
 * Order count column tests with WooCommerce present.
 *
 * @package OptimizationsAceMc
 */

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests for the order count column of the Users screen.
 *
 * Each test runs in its own process, because the WooCommerce function that
 * these tests define cannot be removed again, and the other tests need it to
 * be missing.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState( false )]
final class WooCommerceOrderCountTest extends Oam_Test_Case {

	private const SETTING = 'woocommerce_user_order_count_column';

	/**
	 * Define the WooCommerce function for this process.
	 */
	protected function setUp(): void {
		parent::setUp();
		oam_test_define_woocommerce();
	}

	/**
	 * With WooCommerce, the setting, and an admin screen, the column hooks are added.
	 */
	public function test_column_hooks_are_added_in_the_admin(): void {
		$feature = new Optimizations_Ace_Mc_WooCommerce_Optimizations( $this->settings_enabled( self::SETTING ) );

		$feature->register_hooks();

		$this->assertSame( array( 'manage_users_columns', 'manage_users_custom_column' ), $this->hooked_names() );
		$this->assertSame( 10, has_filter( 'manage_users_columns', array( $feature, 'add_user_order_count_column' ) ) );
		$this->assertSame( 10, has_filter( 'manage_users_custom_column', array( $feature, 'display_user_order_count_column' ) ) );
		$this->assertSame( 3, $GLOBALS['oam_test']['hooks']['manage_users_custom_column'][10][0]['accepted_args'], 'The cell callback needs the column name and the user ID.' );
	}

	/**
	 * The column is an admin feature: nothing is added on the front end.
	 */
	public function test_column_hooks_are_not_added_on_the_front_end(): void {
		$GLOBALS['oam_test']['is_admin'] = false;

		( new Optimizations_Ace_Mc_WooCommerce_Optimizations( $this->settings_enabled( self::SETTING ) ) )->register_hooks();

		$this->assertSame( array(), $this->hooked_names() );
	}

	/**
	 * With the setting off nothing is added, even with WooCommerce present.
	 */
	public function test_column_hooks_need_the_setting(): void {
		( new Optimizations_Ace_Mc_WooCommerce_Optimizations( $this->settings_enabled( 'admin_user_registration_date_column' ) ) )->register_hooks();

		$this->assertSame( array(), $this->hooked_names() );
	}

	/**
	 * The cell shows the order count of the row's user, formatted for the site.
	 */
	public function test_cell_shows_the_formatted_order_count_of_the_user(): void {
		$GLOBALS['oam_test']['order_counts'] = array(
			5 => 1234,
			6 => 0,
		);
		$feature                             = new Optimizations_Ace_Mc_WooCommerce_Optimizations( $this->settings_enabled( self::SETTING ) );

		$first  = $feature->display_user_order_count_column( 'previous output', 'user_order_count', 5 );
		$second = $feature->display_user_order_count_column( '', 'user_order_count', 6 );

		$this->assertSame( array( 5, 6 ), $GLOBALS['oam_test']['order_count_calls'] );
		$this->assertSame( array( 1234, 0 ), $GLOBALS['oam_test']['number_format_calls'] );
		$this->assertSame( esc_html( 'number[1234]' ), $first );
		$this->assertSame( esc_html( 'number[0]' ), $second );
	}

	/**
	 * The formatted number is escaped.
	 */
	public function test_cell_text_is_escaped(): void {
		$GLOBALS['oam_test']['number_format_result'] = '1' . self::MARKUP;
		$feature                                     = new Optimizations_Ace_Mc_WooCommerce_Optimizations( $this->settings_enabled( self::SETTING ) );

		$this->assert_no_raw_markup( $feature->display_user_order_count_column( '', 'user_order_count', 5 ) );
	}
}
