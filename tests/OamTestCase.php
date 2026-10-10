<?php
/**
 * Base test case with helpers for the WordPress stand-in state.
 *
 * @package OptimizationsAceMc
 */

use PHPUnit\Framework\TestCase;

/**
 * Resets the stand-in state before each test and gives access to it.
 *
 * An abstract class rather than a trait: Lizard, which Codacy runs, reads a
 * PHP trait as one long function.
 */
abstract class Oam_Test_Case extends TestCase {

	/**
	 * A translation that appends markup, as a hostile or broken translation file would.
	 */
	protected const MARKUP = '<script>alert(1)</script>';

	/**
	 * Start every test from the default stand-in state.
	 */
	protected function setUp(): void {
		parent::setUp();
		oam_test_reset_state();
	}

	/**
	 * Store settings and return a repository that has read them.
	 *
	 * @param mixed $stored Stored option value.
	 */
	protected function settings_with( mixed $stored ): Optimizations_Ace_Mc_Settings {
		$GLOBALS['oam_test']['options'][ Optimizations_Ace_Mc_Settings::OPTION_NAME ] = $stored;

		return new Optimizations_Ace_Mc_Settings();
	}

	/**
	 * A repository with the named settings on and every other setting off.
	 *
	 * @param string ...$enabled Setting keys.
	 */
	protected function settings_enabled( string ...$enabled ): Optimizations_Ace_Mc_Settings {
		return $this->settings_with( array_fill_keys( $enabled, '1' ) );
	}

	/**
	 * Make every translated string end in markup.
	 */
	protected function use_translation_with_markup(): void {
		$GLOBALS['oam_test']['translation'] = static fn( string $text ): string => $text . self::MARKUP;
	}

	/**
	 * Run a callback and return what it printed.
	 *
	 * @param callable $callback Callback that prints.
	 */
	protected function output_of( callable $callback ): string {
		ob_start();

		try {
			$callback();
		} finally {
			$output = (string) ob_get_clean();
		}

		return $output;
	}

	/**
	 * Parse printed markup.
	 *
	 * @param string $html Markup.
	 */
	protected function dom( string $html ): DOMXPath {
		$document = new DOMDocument();
		$previous = libxml_use_internal_errors( true );
		$document->loadHTML( '<?xml encoding="UTF-8"><div id="oam-test-root">' . $html . '</div>' );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		return new DOMXPath( $document );
	}

	/**
	 * Assert that no markup from a translation or stored value reached the page unescaped.
	 *
	 * @param string $html Printed markup.
	 */
	protected function assert_no_raw_markup( string $html ): void {
		$this->assertStringNotContainsString( '<script', $html );
		$this->assertStringContainsString( '&lt;script&gt;alert(1)&lt;/script&gt;', $html );
	}

	/**
	 * Callbacks recorded for a hook, in registration order.
	 *
	 * @param string $hook Hook name.
	 * @return array<int, mixed>
	 */
	protected function callbacks_on( string $hook ): array {
		$callbacks = array();

		foreach ( $GLOBALS['oam_test']['hooks'][ $hook ] ?? array() as $entries ) {
			foreach ( $entries as $entry ) {
				$callbacks[] = $entry['callback'];
			}
		}

		return $callbacks;
	}

	/**
	 * Names of every hook that has a recorded callback.
	 *
	 * @return array<int, string>
	 */
	protected function hooked_names(): array {
		$names = array_keys( $GLOBALS['oam_test']['hooks'] );
		sort( $names );

		return $names;
	}
}
