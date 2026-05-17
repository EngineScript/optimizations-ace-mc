<?php
/**
 * Tests for settings sanitization.
 *
 * @package OptimizationsAceMc
 */

use PHPUnit\Framework\TestCase;

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', true );
}

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Minimal get_option stub for isolated settings unit tests.
	 *
	 * @param string $option  Option name.
	 * @param mixed  $default Default value.
	 * @return mixed Default value.
	 */
	function get_option( string $option, mixed $default = false ): mixed {
		unset( $option );

		return $default;
	}
}

require_once dirname( __DIR__, 2 ) . '/includes/class-optimizations-ace-mc-settings.php';

/**
 * Settings sanitization tests.
 */
final class SettingsSanitizationTest extends TestCase {

	/**
	 * Known checkbox fields should accept only scalar boolean-like values.
	 */
	public function test_sanitize_settings_accepts_only_known_scalar_boolean_values(): void {
		$settings = new Optimizations_Ace_Mc_Settings();

		$result = $settings->sanitize_settings(
			[
				'woocommerce_show_empty_categories'   => '1',
				'woocommerce_hide_category_count'     => 'off',
				'woocommerce_user_order_count_column' => true,
				'wpsl_show_store_categories'          => 'yes',
				'wpsl_disable_rest_api'               => [ '1' ],
				'admin_user_registration_date_column' => 'unexpected-string',
				'unknown_option'                      => '1',
			]
		);

		self::assertSame(
			[
				'woocommerce_show_empty_categories'   => true,
				'woocommerce_hide_category_count'     => false,
				'woocommerce_user_order_count_column' => true,
				'wpsl_show_store_categories'          => true,
				'wpsl_disable_rest_api'               => false,
				'admin_user_registration_date_column' => false,
			],
			$result
		);
		self::assertArrayNotHasKey( 'unknown_option', $result );
	}
}
