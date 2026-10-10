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
	 * Delete the stored settings when the plugin is deleted.
	 *
	 * Registered with register_uninstall_hook(), which only accepts static callbacks.
	 *
	 * @since 1.6.0
	 */
	public static function uninstall(): void {
		delete_option( self::OPTION_NAME );
	}

	/**
	 * Reload settings from the database.
	 *
	 * @since 1.0.9
	 */
	public function refresh(): void {
		// A stored value that is not an array is treated as nothing stored.
		$this->settings = $this->sanitize_settings( get_option( self::OPTION_NAME, [] ) );
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
	 * Core passes null when every checkbox is cleared, so any non-array input
	 * is treated as an empty submission.
	 *
	 * @since 1.0.9
	 * @param mixed $input Raw input data.
	 * @return array<string, bool> Sanitized settings.
	 */
	public function sanitize_settings( mixed $input ): array {
		if ( ! is_array( $input ) ) {
			$input = [];
		}

		$sanitized = [];

		foreach ( array_keys( self::DEFAULT_SETTINGS ) as $key ) {
			$sanitized[ $key ] = self::is_checked( $input[ $key ] ?? false );
		}

		return $sanitized;
	}

	/**
	 * Read a submitted or stored value as a checkbox state.
	 *
	 * @since 1.6.1
	 * @param mixed $value Raw value.
	 * @return bool Whether the value means "on".
	 */
	private static function is_checked( mixed $value ): bool {
		return is_scalar( $value ) && false !== filter_var( $value, FILTER_VALIDATE_BOOLEAN );
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
			'woocommerce_show_empty_categories' => __( 'Show empty product categories when WooCommerce lists subcategories on classic shop and category pages.', 'optimizations-ace-mc' ),
			'woocommerce_hide_category_count' => __( 'Hide the product count next to category names when WooCommerce lists subcategories on classic shop and category pages.', 'optimizations-ace-mc' ),
			'woocommerce_user_order_count_column' => __( 'Add a column to the WordPress users admin table showing the total number of WooCommerce orders for each user, across all order statuses.', 'optimizations-ace-mc' ),
			'wpsl_show_store_categories' => __( 'Display store categories in the store locator info windows.', 'optimizations-ace-mc' ),
			'wpsl_disable_rest_api' => __( 'Disable the REST API endpoint for the WP Store Locator post type for security.', 'optimizations-ace-mc' ),
			'admin_user_registration_date_column' => __( 'Add a registration date column to the WordPress users admin table.', 'optimizations-ace-mc' ),
			default => '',
		};
	}
}
