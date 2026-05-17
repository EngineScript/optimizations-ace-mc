<?php
/**
 * Settings storage and sanitization.
 *
 * @package OptimizationsAceMc
 * @since   1.0.9
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Settings storage and sanitization.
 *
 * @since 1.0.9
 */
final class Optimizations_Ace_Mc_Settings {

	public const OPTION_GROUP = 'optimizations_ace_mc_group';
	public const OPTION_NAME  = 'optimizations_ace_mc_settings';
	public const PAGE_SLUG    = 'optimizations-ace-mc';

	/**
	 * Default settings.
	 *
	 * @since 1.0.9
	 * @var array<string, bool>
	 */
	private const DEFAULT_SETTINGS = [
		'woocommerce_show_empty_categories'   => false,
		'woocommerce_hide_category_count'     => false,
		'woocommerce_user_order_count_column' => false,
		'wpsl_show_store_categories'          => false,
		'wpsl_disable_rest_api'               => false,
		'admin_user_registration_date_column' => false,
	];

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.9
	 * @var array<string, bool>
	 */
	private array $settings = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.9
	 */
	public function __construct() {
		$this->refresh();
	}

	/**
	 * Reload settings from the database.
	 *
	 * @since 1.0.9
	 */
	public function refresh(): void {
		$saved_settings = get_option( self::OPTION_NAME, [] );
		$this->settings = $this->sanitize_settings( is_array( $saved_settings ) ? $saved_settings : [] );
	}

	/**
	 * Get all default settings.
	 *
	 * @since 1.0.9
	 * @return array<string, bool>
	 */
	public function defaults(): array {
		return self::DEFAULT_SETTINGS;
	}

	/**
	 * Check whether a setting is enabled.
	 *
	 * @since 1.0.9
	 * @param string $key Setting key.
	 * @return bool Setting value.
	 */
	public function is_enabled( string $key ): bool {
		return $this->settings[ $key ] ?? false;
	}

	/**
	 * Check whether a setting key is registered.
	 *
	 * @since 1.0.9
	 * @param string $key Setting key.
	 * @return bool Whether the setting exists.
	 */
	public function has( string $key ): bool {
		return array_key_exists( $key, self::DEFAULT_SETTINGS );
	}

	/**
	 * Sanitize settings.
	 *
	 * @since 1.0.9
	 * @param array<string, mixed> $input Raw input data.
	 * @return array<string, bool> Sanitized settings.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = [];

		foreach ( array_keys( self::DEFAULT_SETTINGS ) as $key ) {
			$value = $input[ $key ] ?? false;

			$sanitized[ $key ] = is_scalar( $value ) && false !== filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		}

		return $sanitized;
	}

	/**
	 * Get the description for a settings field.
	 *
	 * @since 1.0.9
	 * @param string $name Field name.
	 * @return string Field description.
	 */
	public function get_field_description( string $name ): string {
		return match ( $name ) {
			'woocommerce_show_empty_categories' => __( 'Show empty product categories in WooCommerce category listings.', 'optimizations-ace-mc' ),
			'woocommerce_hide_category_count' => __( 'Hide the product count numbers in category listings.', 'optimizations-ace-mc' ),
			'woocommerce_user_order_count_column' => __( 'Add an order count column to the WordPress users admin table.', 'optimizations-ace-mc' ),
			'wpsl_show_store_categories' => __( 'Display store categories in the store locator info windows.', 'optimizations-ace-mc' ),
			'wpsl_disable_rest_api' => __( 'Disable the REST API endpoint for the WP Store Locator post type for security.', 'optimizations-ace-mc' ),
			'admin_user_registration_date_column' => __( 'Add a registration date column to the WordPress users admin table.', 'optimizations-ace-mc' ),
			default => '',
		};
	}
}
