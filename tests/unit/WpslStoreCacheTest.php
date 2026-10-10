<?php
/**
 * Store cache tests with WP Store Locator present.
 *
 * @package OptimizationsAceMc
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests for dropping WP Store Locator's cached store data when the category
 * setting changes.
 *
 * Each test runs in its own process, because the WP Store Locator function
 * that these tests define cannot be removed again, and other tests need it to
 * be missing.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState( false )]
final class WpslStoreCacheTest extends Oam_Test_Case {

	/**
	 * Define the WP Store Locator function for this process.
	 */
	protected function setUp(): void {
		parent::setUp();
		oam_test_define_store_locator();
	}

	/**
	 * The feature; the callbacks compare the saved values and do not read the settings.
	 */
	private function feature(): Optimizations_Ace_Mc_Wpsl_Optimizations {
		return new Optimizations_Ace_Mc_Wpsl_Optimizations( new Optimizations_Ace_Mc_Settings() );
	}

	/**
	 * The cache is dropped exactly when the category setting changes.
	 *
	 * @param mixed $before  Settings before the save.
	 * @param mixed $after   Settings after the save.
	 * @param int   $flushes How often the cache must be dropped.
	 */
	#[DataProvider( 'provide_saves' )]
	public function test_cache_is_dropped_when_the_category_setting_changes( mixed $before, mixed $after, int $flushes ): void {
		$this->feature()->flush_store_cache_on_change( $before, $after );

		$this->assertSame( $flushes, $GLOBALS['oam_test']['store_cache_flushes'] );
	}

	/**
	 * Saves and the number of cache flushes each one needs.
	 *
	 * @return array<string, array{0: mixed, 1: mixed, 2: int}>
	 */
	public static function provide_saves(): array {
		$on  = array( 'wpsl_show_store_categories' => true );
		$off = array( 'wpsl_show_store_categories' => false );

		return array(
			'turned on'             => array( $off, $on, 1 ),
			'turned off'            => array( $on, $off, 1 ),
			'stays on'              => array( $on, $on, 0 ),
			'stays off'             => array( $off, $off, 0 ),
			'another setting only'  => array( $off + array( 'wpsl_disable_rest_api' => false ), $off + array( 'wpsl_disable_rest_api' => true ), 0 ),
			'key missing, then on'  => array( array(), $on, 1 ),
			'broken value, then on' => array( 'broken', $on, 1 ),
			'on, then broken value' => array( $on, null, 1 ),
			'both broken'           => array( null, 'broken', 0 ),
		);
	}

	/**
	 * The first save has no earlier value: the cache is dropped when it turns the setting on.
	 */
	public function test_first_save_drops_the_cache_when_it_turns_the_setting_on(): void {
		$feature = $this->feature();

		$feature->flush_store_cache_on_first_save( 'optimizations_ace_mc_settings', array( 'wpsl_show_store_categories' => false ) );
		$this->assertSame( 0, $GLOBALS['oam_test']['store_cache_flushes'] );

		$feature->flush_store_cache_on_first_save( 'optimizations_ace_mc_settings', array( 'wpsl_show_store_categories' => true ) );
		$this->assertSame( 1, $GLOBALS['oam_test']['store_cache_flushes'] );
	}
}
