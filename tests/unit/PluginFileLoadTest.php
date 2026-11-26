<?php
/**
 * Tests for loading the main plugin file: its constants, class files, and hooks.
 *
 * @package OptimizationsAceMc
 */

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * The bootstrap loads the plugin file before coverage starts. These tests load it in a separate
 * process instead, where the bootstrap skips it (see OAM_TEST_DEFER_PLUGIN_LOAD), so coverage
 * records the file's top-level code.
 */
final class PluginFileLoadTest extends Oam_Test_Case {

	/**
	 * Tell the bootstrap of the separate processes not to load the plugin file.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		putenv( 'OAM_TEST_DEFER_PLUGIN_LOAD=1' );
	}

	/**
	 * Remove the variable, so later processes load the plugin file as usual.
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void {
		putenv( 'OAM_TEST_DEFER_PLUGIN_LOAD' );
		parent::tearDownAfterClass();
	}

	/**
	 * Load the plugin file, after checking the bootstrap did not.
	 *
	 * @return string Plugin file path.
	 */
	private function load_plugin_file(): string {
		$this->assertFalse( defined( 'OPTIMIZATIONS_ACE_MC_PLUGIN_FILE' ), 'The bootstrap loaded the plugin file in this process.' );

		$file = dirname( __DIR__, 2 ) . '/optimizations-ace-mc.php';
		require $file;

		return $file;
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState( false )]
	public function test_loading_the_plugin_file_defines_its_constants_and_registers_its_hooks(): void {
		$file = $this->load_plugin_file();

		$this->assertSame( realpath( $file ), realpath( OPTIMIZATIONS_ACE_MC_PLUGIN_FILE ) );
		$this->assertMatchesRegularExpression( '/^\d+\.\d+\.\d+$/', OPTIMIZATIONS_ACE_MC_VERSION );
		$this->assertSame( 10, has_action( 'plugins_loaded', 'optimizations_ace_mc_init' ) );
		$this->assertCount( 1, $GLOBALS['oam_test']['uninstall_hooks'] );
		$this->assertSame( array( 'Optimizations_Ace_Mc_Settings', 'uninstall' ), $GLOBALS['oam_test']['uninstall_hooks'][0]['callback'] );
		$this->assertInstanceOf( Optimizations_Ace_Mc::class, optimizations_ace_mc() );
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState( false )]
	public function test_loading_the_plugin_file_during_uninstall_does_not_register_the_uninstall_hook_again(): void {
		$GLOBALS['oam_test']['did_actions']['pre_uninstall_plugin'] = 1;

		$this->load_plugin_file();

		$this->assertSame( array(), $GLOBALS['oam_test']['uninstall_hooks'] );
		$this->assertSame( 10, has_action( 'plugins_loaded', 'optimizations_ace_mc_init' ) );
	}
}
