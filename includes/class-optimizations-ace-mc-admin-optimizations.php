<?php
/**
 * WordPress admin optimizations.
 *
 * @package OptimizationsAceMc
 * @since   1.0.9
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * WordPress admin optimizations.
 *
 * @since 1.0.9
 */
final class Optimizations_Ace_Mc_Admin_Optimizations {

	/**
	 * Settings repository.
	 *
	 * @since 1.0.9
	 * @var Optimizations_Ace_Mc_Settings
	 */
	private Optimizations_Ace_Mc_Settings $settings;

	/**
	 * Cached date format string.
	 *
	 * @since 1.0.9
	 * @var string
	 */
	private string $date_format = '';

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
	 * Register WordPress admin hooks.
	 *
	 * @since 1.0.9
	 */
	public function register_hooks(): void {
		if ( ! is_admin() || ! $this->settings->is_enabled( 'admin_user_registration_date_column' ) ) {
			return;
		}

		add_filter( 'manage_users_columns', array( $this, 'add_user_registration_date_column' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'display_user_registration_date_column' ), 10, 3 );
		add_filter( 'manage_users_sortable_columns', array( $this, 'make_user_registration_date_sortable' ) );
	}

	/**
	 * Add registration date column to users table.
	 *
	 * Other plugins share this filter, so a non-array value is passed through unchanged.
	 *
	 * @since 1.0.9
	 * @param mixed $columns Existing columns.
	 * @return mixed
	 */
	public function add_user_registration_date_column( mixed $columns ): mixed {
		if ( ! is_array( $columns ) ) {
			return $columns;
		}

		$columns['registration_date'] = __( 'Registration Date', 'optimizations-ace-mc' );

		return $columns;
	}

	/**
	 * Display registration date in users table.
	 *
	 * @since 1.0.9
	 * @param mixed  $output Custom column output from earlier callbacks.
	 * @param string $column_name Name of the column.
	 * @param int    $user_id User ID.
	 * @return mixed
	 */
	public function display_user_registration_date_column( mixed $output, string $column_name, int $user_id ): mixed {
		if ( 'registration_date' !== $column_name ) {
			return $output;
		}

		$date = $this->get_registration_date( $user_id );

		return esc_html( false === $date ? __( 'Unknown', 'optimizations-ace-mc' ) : $date );
	}

	/**
	 * Make registration date column sortable.
	 *
	 * @since 1.0.9
	 * @param mixed $columns Sortable columns.
	 * @return mixed
	 */
	public function make_user_registration_date_sortable( mixed $columns ): mixed {
		if ( ! is_array( $columns ) ) {
			return $columns;
		}

		$columns['registration_date'] = 'registered';

		return $columns;
	}

	/**
	 * Format a user's registration date in the site's date format, time zone, and language.
	 *
	 * @since 1.6.0
	 * @param int $user_id User ID.
	 * @return string|false Formatted date, or false when the date is missing or invalid.
	 */
	private function get_registration_date( int $user_id ): string|false {
		$user = get_userdata( $user_id );
		if ( false === $user ) {
			return false;
		}

		// MySQL's zero date parses to year -1 instead of failing, so reject it explicitly.
		$registered = $user->user_registered;
		if ( '' === $registered || '0000-00-00 00:00:00' === $registered ) {
			return false;
		}

		// WordPress runs PHP in UTC, so this reads the stored GMT value correctly.
		$timestamp = strtotime( $registered );
		if ( false === $timestamp ) {
			return false;
		}

		if ( '' === $this->date_format ) {
			$date_format       = get_option( 'date_format' );
			$time_format       = get_option( 'time_format' );
			$this->date_format = ( is_string( $date_format ) ? $date_format : '' ) . ' ' . ( is_string( $time_format ) ? $time_format : '' );
		}

		return wp_date( $this->date_format, $timestamp );
	}
}
