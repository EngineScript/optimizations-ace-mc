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
	 * @since 1.0.9
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function add_user_registration_date_column( array $columns ): array {
		$columns['registration_date'] = __( 'Registration Date', 'optimizations-ace-mc' );

		return $columns;
	}

	/**
	 * Display registration date in users table.
	 *
	 * @since 1.0.9
	 * @param string $output Custom column output.
	 * @param string $column_name Name of the column.
	 * @param int    $user_id User ID.
	 * @return string
	 */
	public function display_user_registration_date_column( string $output, string $column_name, int $user_id ): string {
		if ( 'registration_date' !== $column_name ) {
			return $output;
		}

		$user = get_userdata( absint( $user_id ) );
		if ( false === $user || '' === $user->user_registered ) {
			return esc_html__( 'Unknown', 'optimizations-ace-mc' );
		}

		if ( '' === $this->date_format ) {
			$this->date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		}

		return esc_html( get_date_from_gmt( $user->user_registered, $this->date_format ) );
	}

	/**
	 * Make registration date column sortable.
	 *
	 * @since 1.0.9
	 * @param array<string, string> $columns Sortable columns.
	 * @return array<string, string>
	 */
	public function make_user_registration_date_sortable( array $columns ): array {
		$columns['registration_date'] = 'registered';

		return $columns;
	}
}
