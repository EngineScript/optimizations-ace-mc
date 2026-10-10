<?php
/**
 * Info window template tests.
 *
 * @package OptimizationsAceMc
 */

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for what the plugin adds to WP Store Locator's info window template.
 *
 * The templates below are shaped like the ones WP Store Locator builds: a
 * wrapper, some content, and the action links last. The tests check where the
 * categories go and that nothing else changes. Whether the result runs in WP
 * Store Locator's template engine is left to a browser.
 */
final class WpslInfoWindowTemplateTest extends Oam_Test_Case {

	private const ACTIONS = "\t" . '<%= createInfoWindowActions( id, url, typeof permalink !== "undefined" ? permalink : "" ) %>' . "\r\n";

	private const CATEGORIES = "\t" . '<% if ( typeof terms !== "undefined" && terms ) { %>' . "\r\n"
		. "\t" . '<p>Certifications: <%= terms %></p>' . "\r\n"
		. "\t" . '<% } %>' . "\r\n";

	/**
	 * A template as WP Store Locator 3 builds it.
	 */
	private static function store_locator_template(): string {
		return '<div data-store-id="<%= id %>" class="wpsl-info-window">' . "\r\n"
			. "\t" . '<p><strong><%= store %></strong><span class="wpsl-street"><%= address %></span></p>' . "\r\n"
			. "\t" . '<% if ( typeof description !== "undefined" && description ) { %>' . "\r\n"
			. "\t" . '<div><p><%= description %></p></div>' . "\r\n"
			. "\t" . '<% } %>' . "\r\n"
			. self::ACTIONS
			. '</div>';
	}

	/**
	 * The filtered template; the callback does not read the settings.
	 *
	 * @param mixed $template Template from WP Store Locator.
	 */
	private function filtered( mixed $template ): mixed {
		return ( new Optimizations_Ace_Mc_Wpsl_Optimizations( new Optimizations_Ace_Mc_Settings() ) )->customize_info_window_template( $template );
	}

	/**
	 * The categories go in front of the action links, and the rest of the template is kept as it is.
	 */
	public function test_categories_are_added_in_front_of_the_action_links(): void {
		$template = self::store_locator_template();

		$filtered = $this->filtered( $template );

		$this->assertSame( str_replace( self::ACTIONS, self::CATEGORIES . self::ACTIONS, $template ), $filtered );
		$this->assertSame( $template, str_replace( self::CATEGORIES, '', $filtered ), 'Nothing but the categories may change.' );
	}

	/**
	 * Store data cached before the setting was enabled has no category text,
	 * so the template must test that the value exists before it reads it.
	 */
	public function test_categories_are_guarded_against_missing_store_data(): void {
		$filtered = $this->filtered( self::store_locator_template() );

		$this->assertSame( 1, substr_count( $filtered, '<%= terms %>' ) );
		$this->assertMatchesRegularExpression( '/<% if \( typeof terms !== "undefined" && terms \) \{ %>\s*<p>[^<]*<%= terms %><\/p>\s*<% \} %>/', $filtered );
		$this->assertSame( 0, preg_match( '/<% if \( terms \)/', $filtered ), 'An unguarded test of the value stops the info window from opening.' );
	}

	/**
	 * A template without action links gets the categories in front of its closing tag, and any other text at its end.
	 *
	 * @param string $template Template from WP Store Locator or another plugin.
	 * @param string $expected Template with the categories.
	 */
	#[DataProvider( 'provide_templates_without_actions' )]
	public function test_templates_without_action_links_still_get_the_categories( string $template, string $expected ): void {
		$this->assertSame( $expected, $this->filtered( $template ) );
	}

	/**
	 * Templates without the action links.
	 *
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function provide_templates_without_actions(): array {
		$open = '<div class="wpsl-info-window">' . "\r\n\t" . '<div class="inner"><%= store %></div>' . "\r\n";

		return array(
			'wrapper only'   => array( $open . '</div>', $open . self::CATEGORIES . '</div>' ),
			'indented close' => array( $open . "\t" . '</div>', $open . self::CATEGORIES . "\t" . '</div>' ),
			'no wrapper'     => array( '<%= store %>', '<%= store %>' . self::CATEGORIES ),
			'empty'          => array( '', self::CATEGORIES ),
		);
	}

	/**
	 * A template of the wrong type, from another plugin, is passed on unchanged.
	 */
	public function test_templates_of_the_wrong_type_pass_through(): void {
		foreach ( array( null, false, 7, array( '<div></div>' ) ) as $foreign ) {
			$this->assertSame( $foreign, $this->filtered( $foreign ) );
		}
	}

	/**
	 * The plugin no longer builds the template itself, so it does not call WP Store Locator's template helpers.
	 */
	public function test_template_is_filtered_without_store_locator_helpers(): void {
		$this->assertFalse( function_exists( 'wpsl_store_header_template' ) );
		$this->assertFalse( function_exists( 'wpsl_address_format_placeholders' ) );

		$this->assertIsString( $this->filtered( self::store_locator_template() ) );
	}

	/**
	 * Another plugin or the theme can replace the label.
	 */
	public function test_label_can_be_replaced_with_the_filter(): void {
		add_filter( 'optimizations_ace_mc_store_category_label', static fn( string $label ): string => 'Dealer type (was ' . $label . ')' );

		$this->assertStringContainsString( '<p>Dealer type (was Certifications:) <%= terms %></p>', $this->filtered( self::store_locator_template() ) );
	}

	/**
	 * A label of the wrong type, from a broken filter, is left out instead of failing.
	 */
	public function test_label_of_the_wrong_type_is_left_out(): void {
		add_filter( 'optimizations_ace_mc_store_category_label', static fn(): array => array( 'Label' ) );

		$this->assertStringContainsString( '<p> <%= terms %></p>', $this->filtered( '' ) );
	}

	/**
	 * Markup in a translated or filtered label is printed as text.
	 */
	public function test_label_is_escaped(): void {
		$this->use_translation_with_markup();
		$this->assert_no_raw_markup( $this->filtered( '' ) );

		oam_test_reset_state();
		add_filter( 'optimizations_ace_mc_store_category_label', static fn(): string => 'Label' . self::MARKUP );
		$this->assert_no_raw_markup( $this->filtered( '' ) );
	}
}
