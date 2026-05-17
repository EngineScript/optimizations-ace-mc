<?php
/**
 * Tests for project metadata that donor files commonly drift from.
 *
 * @package OptimizationsAceMc
 */

use PHPUnit\Framework\TestCase;

/**
 * Plugin metadata tests.
 */
final class PluginMetadataTest extends TestCase {

	/**
	 * Plugin file contents.
	 *
	 * @var string
	 */
	private string $plugin_file_contents;

	/**
	 * Load the plugin file contents.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->plugin_file_contents = (string) file_get_contents( dirname( __DIR__, 2 ) . '/optimizations-ace-mc.php' );
	}

	/**
	 * The plugin header should advertise current support floors.
	 */
	public function test_plugin_header_uses_current_minimum_versions(): void {
		self::assertStringContainsString( 'Requires at least: 6.8', $this->plugin_file_contents );
		self::assertStringContainsString( 'Requires PHP: 8.2', $this->plugin_file_contents );
	}

	/**
	 * The main plugin file should use this plugin's slug and constants.
	 */
	public function test_plugin_header_uses_project_identity(): void {
		self::assertStringContainsString( 'Text Domain: optimizations-ace-mc', $this->plugin_file_contents );
		self::assertStringContainsString( 'OPTIMIZATIONS_ACE_MC_VERSION', $this->plugin_file_contents );
		self::assertStringContainsString( '/includes/class-optimizations-ace-mc.php', $this->plugin_file_contents );
	}
}
