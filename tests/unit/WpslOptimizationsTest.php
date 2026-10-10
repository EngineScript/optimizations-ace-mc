<?php
/**
 * WP Store Locator feature tests.
 *
 * @package OptimizationsAceMc
 */

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for the store locator hooks, the REST switch, and the category text.
 *
 * WpslInfoWindowTemplateTest covers what the plugin adds to the info window
 * template, and WpslStoreCacheTest the cache flush with WP Store Locator
 * present. Whether the finished template runs in WP Store Locator's template
 * engine is left to a browser.
 */
final class WpslOptimizationsTest extends Oam_Test_Case {

	private const TAXONOMY = 'wpsl_store_category';

	/**
	 * The feature with no setting on; the filter callbacks do not read the settings.
	 */
	private function feature(): Optimizations_Ace_Mc_Wpsl_Optimizations {
		return new Optimizations_Ace_Mc_Wpsl_Optimizations( new Optimizations_Ace_Mc_Settings() );
	}

	/**
	 * A term as WordPress returns it.
	 *
	 * @param mixed $name Term name.
	 */
	private static function term( mixed $name ): object {
		return (object) array(
			'term_id' => 1,
			'name'    => $name,
		);
	}

	/**
	 * Each setting adds its own WP Store Locator filters and no other. A
	 * front-end request is used, because the admin adds the cache hooks too.
	 *
	 * @param array<int, string> $enabled Settings that are on.
	 * @param array<int, string> $hooks   Hooks that must be added.
	 */
	#[DataProvider( 'provide_settings' )]
	public function test_settings_add_their_filters( array $enabled, array $hooks ): void {
		$GLOBALS['oam_test']['is_admin'] = false;
		$feature                         = new Optimizations_Ace_Mc_Wpsl_Optimizations( $this->settings_enabled( ...$enabled ) );

		$feature->register_hooks();

		$this->assertSame( $hooks, $this->hooked_names() );
	}

	/**
	 * Settings and the filters they add.
	 *
	 * @return array<string, array{0: array<int, string>, 1: array<int, string>}>
	 */
	public static function provide_settings(): array {
		return array(
			'nothing on'         => array( array(), array() ),
			'categories'         => array( array( 'wpsl_show_store_categories' ), array( 'wpsl_info_window_template', 'wpsl_store_meta' ) ),
			'REST switch'        => array( array( 'wpsl_disable_rest_api' ), array( 'wpsl_post_type_args' ) ),
			'both'               => array( array( 'wpsl_show_store_categories', 'wpsl_disable_rest_api' ), array( 'wpsl_info_window_template', 'wpsl_post_type_args', 'wpsl_store_meta' ) ),
			'unrelated settings' => array( array( 'woocommerce_hide_category_count', 'admin_user_registration_date_column' ), array() ),
		);
	}

	/**
	 * The filters are bound to the feature's own callbacks; the meta filter receives the store ID.
	 */
	public function test_filters_are_bound_to_the_feature(): void {
		$feature = new Optimizations_Ace_Mc_Wpsl_Optimizations( $this->settings_enabled( 'wpsl_show_store_categories', 'wpsl_disable_rest_api' ) );

		$feature->register_hooks();

		$this->assertSame( 10, has_filter( 'wpsl_store_meta', array( $feature, 'add_store_categories_to_meta' ) ) );
		$this->assertSame( 2, $GLOBALS['oam_test']['hooks']['wpsl_store_meta'][10][0]['accepted_args'] );
		$this->assertSame( 10, has_filter( 'wpsl_info_window_template', array( $feature, 'customize_info_window_template' ) ) );
		$this->assertSame( 10, has_filter( 'wpsl_post_type_args', array( $feature, 'disable_store_locator_rest_api' ) ) );
	}

	/**
	 * The store locator features work for visitors: they do not need the admin.
	 */
	public function test_filters_do_not_need_the_admin(): void {
		$GLOBALS['oam_test']['is_admin'] = false;

		( new Optimizations_Ace_Mc_Wpsl_Optimizations( $this->settings_enabled( 'wpsl_show_store_categories', 'wpsl_disable_rest_api' ) ) )->register_hooks();

		$this->assertSame( array( 'wpsl_info_window_template', 'wpsl_post_type_args', 'wpsl_store_meta' ), $this->hooked_names() );
	}

	/**
	 * In the admin, where the settings are saved, the feature also listens for a change of the settings.
	 */
	public function test_admin_adds_the_cache_hooks_whatever_the_settings(): void {
		foreach ( array( array(), array( 'wpsl_show_store_categories' ) ) as $enabled ) {
			oam_test_reset_state();
			$feature = new Optimizations_Ace_Mc_Wpsl_Optimizations( $this->settings_enabled( ...$enabled ) );

			$feature->register_hooks();

			$this->assertSame( 10, has_action( 'update_option_optimizations_ace_mc_settings', array( $feature, 'flush_store_cache_on_change' ) ) );
			$this->assertSame( 10, has_action( 'add_option_optimizations_ace_mc_settings', array( $feature, 'flush_store_cache_on_first_save' ) ) );
			$this->assertSame( 2, $GLOBALS['oam_test']['hooks']['update_option_optimizations_ace_mc_settings'][10][0]['accepted_args'] );
			$this->assertSame( 2, $GLOBALS['oam_test']['hooks']['add_option_optimizations_ace_mc_settings'][10][0]['accepted_args'] );
		}
	}

	/**
	 * Without WP Store Locator a change of the settings is not an error.
	 */
	public function test_settings_change_without_store_locator_does_nothing(): void {
		$this->assertFalse( function_exists( 'wpsl_flush_store_cache' ), 'WP Store Locator must not be defined in this process.' );
		$feature = $this->feature();

		$feature->flush_store_cache_on_change( array(), array( 'wpsl_show_store_categories' => true ) );
		$feature->flush_store_cache_on_first_save( 'optimizations_ace_mc_settings', array( 'wpsl_show_store_categories' => true ) );

		$this->assertSame( 0, $GLOBALS['oam_test']['store_cache_flushes'] );
	}

	/**
	 * Store meta of the wrong type, from another plugin, is passed on unchanged and no terms are read.
	 */
	public function test_store_meta_of_the_wrong_type_passes_through(): void {
		foreach ( array( null, false, 'meta', 7 ) as $foreign ) {
			$this->assertSame( $foreign, $this->feature()->add_store_categories_to_meta( $foreign, 15 ) );
		}
		$this->assertSame( array(), $GLOBALS['oam_test']['term_queries'] );
	}

	/**
	 * A store ID of the wrong type, or a missing one, reads no store's terms instead of failing.
	 */
	public function test_store_id_of_the_wrong_type_is_read_as_no_store(): void {
		$GLOBALS['oam_test']['terms'][1] = array( self::term( 'Wrong Store' ) );

		$this->assertSame( array( 'terms' => '' ), $this->feature()->add_store_categories_to_meta( array(), array( 15 ) ) );
		$this->assertSame( array( 'terms' => '' ), $this->feature()->add_store_categories_to_meta( array(), null ) );
		$this->assertSame( array( 'terms' => '' ), $this->feature()->add_store_categories_to_meta( array() ) );
		$this->assertSame( array( 0, 0, 0 ), array_column( $GLOBALS['oam_test']['term_queries'], 'post_id' ) );
	}

	/**
	 * A numeric store ID given as text, as a filter may pass it, is used as a number.
	 */
	public function test_store_id_given_as_text_is_used(): void {
		$GLOBALS['oam_test']['terms'][15] = array( self::term( 'Found' ) );

		$this->assertSame( array( 'terms' => 'Found' ), $this->feature()->add_store_categories_to_meta( array(), '15' ) );
	}

	/**
	 * The REST switch turns show_in_rest off and leaves every other argument alone.
	 */
	public function test_rest_switch_turns_show_in_rest_off(): void {
		$arguments = array(
			'public'       => true,
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor' ),
		);

		$filtered = $this->feature()->disable_store_locator_rest_api( $arguments );

		$this->assertSame( array_merge( $arguments, array( 'show_in_rest' => false ) ), $filtered );
		$this->assertSame( array( 'show_in_rest' => false ), $this->feature()->disable_store_locator_rest_api( array() ), 'The key is set even when the post type did not name it.' );
	}

	/**
	 * Post type arguments of the wrong type, from another plugin, are passed on unchanged.
	 */
	public function test_rest_switch_passes_foreign_values_through(): void {
		foreach ( array( null, false, 'arguments', 7 ) as $foreign ) {
			$this->assertSame( $foreign, $this->feature()->disable_store_locator_rest_api( $foreign ) );
		}
	}

	/**
	 * Category names are joined with a comma, in the order of the terms, for the store that was asked for.
	 */
	public function test_category_names_are_joined_for_the_store(): void {
		$GLOBALS['oam_test']['terms'][15] = array( self::term( 'Certified Installer' ), self::term( 'Service Center' ) );
		$GLOBALS['oam_test']['terms'][16] = array( self::term( 'Other Store' ) );

		$meta = $this->feature()->add_store_categories_to_meta( array( 'store' => 'Probe Store' ), 15 );

		$this->assertSame(
			array(
				'store' => 'Probe Store',
				'terms' => 'Certified Installer, Service Center',
			),
			$meta
		);
		$this->assertSame(
			array(
				array(
					'post_id'  => 15,
					'taxonomy' => self::TAXONOMY,
				),
			),
			$GLOBALS['oam_test']['term_queries']
		);
	}

	/**
	 * A store without categories gets an empty text, so that the template can test it.
	 *
	 * @param mixed $terms What the term lookup returned.
	 */
	#[DataProvider( 'provide_no_terms' )]
	public function test_store_without_categories_gets_an_empty_text( mixed $terms ): void {
		$GLOBALS['oam_test']['terms'][15] = $terms;

		$meta = $this->feature()->add_store_categories_to_meta( array( 'terms' => 'left over from another filter' ), 15 );

		$this->assertSame( array( 'terms' => '' ), $meta );
	}

	/**
	 * Results of a term lookup that hold no category.
	 *
	 * @return array<string, array{0: mixed}>
	 */
	public static function provide_no_terms(): array {
		return array(
			'no terms'         => array( false ),
			'lookup error'     => array( new WP_Error() ),
			'empty list'       => array( array() ),
			'only empty names' => array( array( self::term( '' ), self::term( '' ) ) ),
		);
	}

	/**
	 * Empty names are left out instead of leaving a stray comma.
	 */
	public function test_empty_category_names_are_left_out(): void {
		$GLOBALS['oam_test']['terms'][15] = array( self::term( 'First' ), self::term( '' ), self::term( 'Last' ) );

		$meta = $this->feature()->add_store_categories_to_meta( array(), 15 );

		$this->assertSame( 'First, Last', $meta['terms'] );
	}

	/**
	 * A name that is not text, which only a broken term filter could produce, is left out.
	 */
	public function test_category_names_that_are_not_text_are_left_out(): void {
		$GLOBALS['oam_test']['terms'][15] = array( self::term( 'Text' ), self::term( 42 ), self::term( array( 'x' ) ) );

		$meta = $this->feature()->add_store_categories_to_meta( array(), 15 );

		$this->assertSame( 'Text', $meta['terms'] );
	}

	/**
	 * The names are escaped, because the template prints the text as markup.
	 */
	public function test_category_names_are_escaped(): void {
		$GLOBALS['oam_test']['terms'][15] = array( self::term( 'Sales & Service' ), self::term( 'Name' . self::MARKUP ) );

		$meta = $this->feature()->add_store_categories_to_meta( array(), 15 );

		$this->assertStringStartsWith( 'Sales &amp; Service, Name', $meta['terms'] );
		$this->assert_no_raw_markup( $meta['terms'] );
	}

	/**
	 * A negative store ID is made positive before the lookup, as WordPress post IDs are.
	 */
	public function test_store_id_is_made_non_negative(): void {
		$GLOBALS['oam_test']['terms'][15] = array( self::term( 'Found' ) );

		$meta = $this->feature()->add_store_categories_to_meta( array(), -15 );

		$this->assertSame( 'Found', $meta['terms'] );
		$this->assertSame( 15, $GLOBALS['oam_test']['term_queries'][0]['post_id'] );
	}
}
