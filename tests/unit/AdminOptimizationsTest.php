<?php
/**
 * Registration date column tests.
 *
 * @package OptimizationsAceMc
 */

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for the registration date column of the Users screen.
 */
final class AdminOptimizationsTest extends Oam_Test_Case {

	private const SETTING = 'admin_user_registration_date_column';

	/**
	 * The feature with its setting on.
	 */
	private function feature(): Optimizations_Ace_Mc_Admin_Optimizations {
		return new Optimizations_Ace_Mc_Admin_Optimizations( $this->settings_enabled( self::SETTING ) );
	}

	/**
	 * Store a user with a registration date.
	 *
	 * @param int    $user_id    User ID.
	 * @param string $registered Stored registration date.
	 */
	private function add_user( int $user_id, string $registered ): void {
		$GLOBALS['oam_test']['users'][ $user_id ] = (object) array( 'user_registered' => $registered );
	}

	/**
	 * The column hooks are added only in the admin, and only when the setting is on.
	 *
	 * @param bool $is_admin Whether the request is for an admin screen.
	 * @param bool $enabled  Whether the setting is on.
	 * @param bool $expected Whether the hooks are added.
	 */
	#[DataProvider( 'provide_hook_conditions' )]
	public function test_hooks_need_the_admin_and_the_setting( bool $is_admin, bool $enabled, bool $expected ): void {
		$GLOBALS['oam_test']['is_admin'] = $is_admin;
		$settings                        = $enabled ? $this->settings_enabled( self::SETTING ) : $this->settings_with( array() );
		$feature                         = new Optimizations_Ace_Mc_Admin_Optimizations( $settings );

		$feature->register_hooks();

		if ( ! $expected ) {
			$this->assertSame( array(), $this->hooked_names() );
			return;
		}

		$this->assertSame( array( 'manage_users_columns', 'manage_users_custom_column', 'manage_users_sortable_columns' ), $this->hooked_names() );
		$this->assertSame( 10, has_filter( 'manage_users_columns', array( $feature, 'add_user_registration_date_column' ) ) );
		$this->assertSame( 10, has_filter( 'manage_users_custom_column', array( $feature, 'display_user_registration_date_column' ) ) );
		$this->assertSame( 10, has_filter( 'manage_users_sortable_columns', array( $feature, 'make_user_registration_date_sortable' ) ) );
		$this->assertSame( 3, $GLOBALS['oam_test']['hooks']['manage_users_custom_column'][10][0]['accepted_args'], 'The cell callback needs the column name and the user ID.' );
	}

	/**
	 * Admin and setting combinations.
	 *
	 * @return array<string, array{0: bool, 1: bool, 2: bool}>
	 */
	public static function provide_hook_conditions(): array {
		return array(
			'admin, setting on'     => array( true, true, true ),
			'admin, setting off'    => array( true, false, false ),
			'front end, setting on' => array( false, true, false ),
			'front end, off'        => array( false, false, false ),
		);
	}

	/**
	 * Another setting being on does not add this column.
	 */
	public function test_other_settings_do_not_add_the_column(): void {
		$feature = new Optimizations_Ace_Mc_Admin_Optimizations( $this->settings_enabled( 'woocommerce_user_order_count_column', 'wpsl_disable_rest_api' ) );

		$feature->register_hooks();

		$this->assertSame( array(), $this->hooked_names() );
	}

	/**
	 * The column is appended and the existing columns are kept.
	 */
	public function test_column_is_added_after_the_existing_columns(): void {
		$columns = $this->feature()->add_user_registration_date_column(
			array(
				'username' => 'Username',
				'email'    => 'Email',
			)
		);

		$this->assertSame( array( 'username', 'email', 'registration_date' ), array_keys( $columns ) );
		$this->assertSame( 'Username', $columns['username'] );
		$this->assertNotSame( '', $columns['registration_date'] );
	}

	/**
	 * The column sorts by the registration date that WordPress knows as "registered".
	 */
	public function test_column_sorts_by_registered(): void {
		$sortable = $this->feature()->make_user_registration_date_sortable( array( 'email' => 'email' ) );

		$this->assertSame(
			array(
				'email'             => 'email',
				'registration_date' => 'registered',
			),
			$sortable
		);
	}

	/**
	 * A column list of the wrong type, from another plugin, is passed on unchanged.
	 *
	 * @param mixed $foreign Value another plugin returned.
	 */
	#[DataProvider( 'provide_foreign_column_lists' )]
	public function test_column_lists_of_the_wrong_type_pass_through( mixed $foreign ): void {
		$feature = $this->feature();

		$this->assertSame( $foreign, $feature->add_user_registration_date_column( $foreign ) );
		$this->assertSame( $foreign, $feature->make_user_registration_date_sortable( $foreign ) );
	}

	/**
	 * Column lists that are not arrays.
	 *
	 * @return array<string, array{0: mixed}>
	 */
	public static function provide_foreign_column_lists(): array {
		return array(
			'null'   => array( null ),
			'false'  => array( false ),
			'string' => array( 'columns' ),
			'int'    => array( 7 ),
		);
	}

	/**
	 * Cells of other columns are passed on unchanged, whatever their type, without a user lookup.
	 */
	public function test_cells_of_other_columns_pass_through(): void {
		$feature = $this->feature();
		$this->add_user( 5, '2024-01-15 10:30:00' );

		foreach ( array( '', '<b>kept</b>', null, false, 12, array( 'x' ) ) as $output ) {
			foreach ( array( 'user_order_count', 'email', '', 'registration_date ' ) as $column ) {
				$this->assertSame( $output, $feature->display_user_registration_date_column( $output, $column, 5 ) );
			}
		}
		$this->assertSame( array(), $GLOBALS['oam_test']['wp_date_calls'] );
	}

	/**
	 * A stored date is formatted with the site's date and time formats, from the stored GMT time.
	 */
	public function test_date_uses_the_site_formats_and_the_stored_time(): void {
		$GLOBALS['oam_test']['options']['date_format'] = 'Y/m/d';
		$GLOBALS['oam_test']['options']['time_format'] = 'H:i';
		$this->add_user( 5, '2024-01-15 10:30:00' );

		$cell = $this->feature()->display_user_registration_date_column( 'previous output', 'registration_date', 5 );

		$this->assertSame(
			array(
				array(
					'format'    => 'Y/m/d H:i',
					'timestamp' => gmmktime( 10, 30, 0, 1, 15, 2024 ),
				),
			),
			$GLOBALS['oam_test']['wp_date_calls']
		);
		$this->assertSame( esc_html( 'date[Y/m/d H:i|' . gmmktime( 10, 30, 0, 1, 15, 2024 ) . ']' ), $cell );
	}

	/**
	 * The formats are read once and reused for every row of the table.
	 */
	public function test_formats_are_read_once_per_request(): void {
		$GLOBALS['oam_test']['options']['date_format'] = 'Y-m-d';
		$GLOBALS['oam_test']['options']['time_format'] = 'H:i';
		$this->add_user( 5, '2024-01-15 10:30:00' );
		$this->add_user( 6, '2023-06-01 00:00:00' );
		$feature = $this->feature();

		$feature->display_user_registration_date_column( '', 'registration_date', 5 );
		$feature->display_user_registration_date_column( '', 'registration_date', 6 );

		$reads = array_count_values( $GLOBALS['oam_test']['option_reads'] );
		$this->assertSame( 1, $reads['date_format'] );
		$this->assertSame( 1, $reads['time_format'] );
		$this->assertSame( array( 'Y-m-d H:i', 'Y-m-d H:i' ), array_column( $GLOBALS['oam_test']['wp_date_calls'], 'format' ) );
	}

	/**
	 * A format option of the wrong type is read as empty instead of failing.
	 */
	public function test_format_options_of_the_wrong_type_are_ignored(): void {
		$GLOBALS['oam_test']['options']['date_format'] = array( 'Y' );
		$GLOBALS['oam_test']['options']['time_format'] = 12;
		$this->add_user( 5, '2024-01-15 10:30:00' );

		$this->feature()->display_user_registration_date_column( '', 'registration_date', 5 );

		$this->assertSame( array( ' ' ), array_column( $GLOBALS['oam_test']['wp_date_calls'], 'format' ) );
	}

	/**
	 * A user without a usable registration date shows "Unknown", and no date is formatted.
	 *
	 * @param string|null $registered Stored date, or null for a user that does not exist.
	 */
	#[DataProvider( 'provide_unusable_dates' )]
	public function test_unusable_dates_show_unknown( ?string $registered ): void {
		if ( null !== $registered ) {
			$this->add_user( 5, $registered );
		}

		$cell = $this->feature()->display_user_registration_date_column( '', 'registration_date', 5 );

		$this->assertSame( 'Unknown', $cell );
		$this->assertSame( array(), $GLOBALS['oam_test']['wp_date_calls'] );
	}

	/**
	 * Registration dates that cannot be shown.
	 *
	 * @return array<string, array{0: string|null}>
	 */
	public static function provide_unusable_dates(): array {
		return array(
			'no such user'   => array( null ),
			'empty'          => array( '' ),
			'MySQL zero'     => array( '0000-00-00 00:00:00' ),
			'not a date'     => array( 'not a date' ),
			'impossible day' => array( '2024-13-45 99:99:99' ),
		);
	}

	/**
	 * The formatted date and the "Unknown" text are escaped.
	 */
	public function test_cell_text_is_escaped(): void {
		$feature = $this->feature();
		$this->add_user( 5, '2024-01-15 10:30:00' );
		$GLOBALS['oam_test']['wp_date_result'] = 'January' . self::MARKUP;

		$this->assert_no_raw_markup( $feature->display_user_registration_date_column( '', 'registration_date', 5 ) );

		$this->use_translation_with_markup();
		$this->assert_no_raw_markup( $feature->display_user_registration_date_column( '', 'registration_date', 404 ) );
	}
}
