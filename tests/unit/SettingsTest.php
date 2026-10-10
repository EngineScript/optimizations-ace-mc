<?php
/**
 * Settings repository tests.
 *
 * @package OptimizationsAceMc
 */

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for reading, normalizing, and describing the plugin settings.
 */
final class SettingsTest extends Oam_Test_Case {

	private const KEYS = array(
		'woocommerce_show_empty_categories',
		'woocommerce_hide_category_count',
		'woocommerce_user_order_count_column',
		'wpsl_show_store_categories',
		'wpsl_disable_rest_api',
		'admin_user_registration_date_column',
	);

	/**
	 * The option, group, and page names are part of the stored data and the admin URL.
	 */
	public function test_names_are_stable(): void {
		$this->assertSame( 'optimizations_ace_mc_settings', Optimizations_Ace_Mc_Settings::OPTION_NAME );
		$this->assertSame( 'optimizations_ace_mc_group', Optimizations_Ace_Mc_Settings::OPTION_GROUP );
		$this->assertSame( 'optimizations-ace-mc', Optimizations_Ace_Mc_Settings::PAGE_SLUG );
	}

	/**
	 * The six settings exist, in the order of the settings page, and are all off.
	 */
	public function test_defaults_list_the_six_settings_all_off(): void {
		$settings = new Optimizations_Ace_Mc_Settings();

		$this->assertSame( array_fill_keys( self::KEYS, false ), $settings->defaults() );
	}

	/**
	 * With nothing stored, every setting reads as off.
	 */
	public function test_nothing_stored_means_everything_off(): void {
		$settings = new Optimizations_Ace_Mc_Settings();

		$this->assertContains( Optimizations_Ace_Mc_Settings::OPTION_NAME, $GLOBALS['oam_test']['option_reads'] );
		foreach ( self::KEYS as $key ) {
			$this->assertFalse( $settings->is_enabled( $key ), $key );
		}
	}

	/**
	 * Stored values are read per key; keys that are not stored stay off.
	 */
	public function test_stored_values_are_read_per_key(): void {
		$settings = $this->settings_with(
			array(
				'woocommerce_hide_category_count' => true,
				'wpsl_disable_rest_api'           => '1',
			)
		);

		foreach ( self::KEYS as $key ) {
			$this->assertSame( in_array( $key, array( 'woocommerce_hide_category_count', 'wpsl_disable_rest_api' ), true ), $settings->is_enabled( $key ), $key );
		}
	}

	/**
	 * A stored value that is not an array is treated as nothing stored.
	 *
	 * @param mixed $stored Stored option value.
	 */
	#[DataProvider( 'provide_broken_stored_values' )]
	public function test_stored_value_of_the_wrong_type_means_everything_off( mixed $stored ): void {
		$settings = $this->settings_with( $stored );

		foreach ( self::KEYS as $key ) {
			$this->assertFalse( $settings->is_enabled( $key ), $key );
		}
	}

	/**
	 * Stored option values of the wrong type.
	 *
	 * @return array<string, array{0: mixed}>
	 */
	public static function provide_broken_stored_values(): array {
		return array(
			'string'  => array( 'yes' ),
			'true'    => array( true ),
			'integer' => array( 1 ),
			'null'    => array( null ),
			'object'  => array( (object) array( 'wpsl_disable_rest_api' => true ) ),
		);
	}

	/**
	 * A key that is not a setting is off and is not reported as a setting.
	 */
	public function test_unknown_key_is_off_and_not_a_setting(): void {
		$settings = $this->settings_with( array( 'unknown_option' => true ) );

		$this->assertFalse( $settings->is_enabled( 'unknown_option' ) );
		$this->assertFalse( $settings->has( 'unknown_option' ) );
		$this->assertFalse( $settings->has( '' ) );
		foreach ( self::KEYS as $key ) {
			$this->assertTrue( $settings->has( $key ), $key );
		}
	}

	/**
	 * refresh() reads the option again.
	 */
	public function test_refresh_reads_the_option_again(): void {
		$settings = new Optimizations_Ace_Mc_Settings();
		$this->assertFalse( $settings->is_enabled( 'wpsl_disable_rest_api' ) );

		$GLOBALS['oam_test']['options'][ Optimizations_Ace_Mc_Settings::OPTION_NAME ] = array( 'wpsl_disable_rest_api' => true );
		$this->assertFalse( $settings->is_enabled( 'wpsl_disable_rest_api' ), 'The value is cached until refresh().' );

		$settings->refresh();
		$this->assertTrue( $settings->is_enabled( 'wpsl_disable_rest_api' ) );
	}

	/**
	 * Each submitted value is read as a boolean; anything else is off.
	 *
	 * @param mixed $value    Submitted value.
	 * @param bool  $expected Stored result.
	 */
	#[DataProvider( 'provide_submitted_values' )]
	public function test_submitted_value_is_read_as_a_boolean( mixed $value, bool $expected ): void {
		$settings = new Optimizations_Ace_Mc_Settings();
		$result   = $settings->sanitize_settings( array( 'wpsl_show_store_categories' => $value ) );

		$this->assertSame( $expected, $result['wpsl_show_store_categories'] );
	}

	/**
	 * Submitted values and the boolean each one means.
	 *
	 * @return array<string, array{0: mixed, 1: bool}>
	 */
	public static function provide_submitted_values(): array {
		return array(
			'checkbox value 1' => array( '1', true ),
			'true'             => array( true, true ),
			'integer 1'        => array( 1, true ),
			'float 1'          => array( 1.0, true ),
			'word true'        => array( 'true', true ),
			'word on'          => array( 'on', true ),
			'word yes'         => array( 'yes', true ),
			'upper case'       => array( 'TRUE', true ),
			'zero string'      => array( '0', false ),
			'false'            => array( false, false ),
			'integer 0'        => array( 0, false ),
			'word false'       => array( 'false', false ),
			'word off'         => array( 'off', false ),
			'word no'          => array( 'no', false ),
			'empty string'     => array( '', false ),
			'other word'       => array( 'enabled', false ),
			'integer 2'        => array( 2, false ),
			'null'             => array( null, false ),
			'array'            => array( array( '1' ), false ),
			'nested true'      => array( array( true ), false ),
			'object'           => array( (object) array( 'value' => '1' ), false ),
		);
	}

	/**
	 * The result always holds exactly the six settings, whatever was submitted.
	 */
	public function test_sanitized_result_always_holds_exactly_the_six_settings(): void {
		$settings = new Optimizations_Ace_Mc_Settings();
		$result   = $settings->sanitize_settings(
			array(
				'admin_user_registration_date_column' => '1',
				'unknown_option'                      => '1',
				0                                     => '1',
			)
		);

		$this->assertSame( self::KEYS, array_keys( $result ) );
		$this->assertSame( array( 'admin_user_registration_date_column' => true ), array_filter( $result ) );
	}

	/**
	 * Each setting has a description, and an unknown name has none.
	 */
	public function test_each_setting_has_its_own_description(): void {
		$settings     = new Optimizations_Ace_Mc_Settings();
		$descriptions = array();

		foreach ( self::KEYS as $key ) {
			$descriptions[ $key ] = $settings->get_field_description( $key );
			$this->assertNotSame( '', $descriptions[ $key ], $key );
		}

		$this->assertCount( 6, array_unique( $descriptions ) );
		$this->assertSame( '', $settings->get_field_description( 'unknown_option' ) );
		$this->assertSame( '', $settings->get_field_description( '' ) );
	}

	/**
	 * Descriptions go through the translation of the site, and are returned unescaped for the caller to escape.
	 */
	public function test_descriptions_are_translated(): void {
		$this->use_translation_with_markup();
		$settings = new Optimizations_Ace_Mc_Settings();

		foreach ( self::KEYS as $key ) {
			$this->assertStringEndsWith( self::MARKUP, $settings->get_field_description( $key ), $key );
		}
	}

	/**
	 * Deleting the plugin deletes its one option.
	 */
	public function test_uninstall_deletes_the_settings_option(): void {
		$GLOBALS['oam_test']['options'][ Optimizations_Ace_Mc_Settings::OPTION_NAME ] = array( 'wpsl_disable_rest_api' => true );

		Optimizations_Ace_Mc_Settings::uninstall();

		$this->assertSame( array( 'optimizations_ace_mc_settings' ), $GLOBALS['oam_test']['deleted_options'] );
	}
}
