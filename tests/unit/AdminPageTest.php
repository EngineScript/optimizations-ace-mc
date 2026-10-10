<?php
/**
 * Settings page tests.
 *
 * @package OptimizationsAceMc
 */

/**
 * Tests for the menu entry, the registered settings, and the page markup.
 */
final class AdminPageTest extends Oam_Test_Case {

	private const FIELDS = array(
		'woocommerce_show_empty_categories'   => 'woocommerce_section',
		'woocommerce_hide_category_count'     => 'woocommerce_section',
		'woocommerce_user_order_count_column' => 'woocommerce_section',
		'wpsl_show_store_categories'          => 'wpsl_section',
		'wpsl_disable_rest_api'               => 'wpsl_section',
		'admin_user_registration_date_column' => 'admin_section',
	);

	/**
	 * Build the page, register its menu entry and settings, and return it.
	 *
	 * @param Optimizations_Ace_Mc_Settings|null $settings Settings to show.
	 */
	private function registered_page( ?Optimizations_Ace_Mc_Settings $settings = null ): Optimizations_Ace_Mc_Admin_Page {
		$page = new Optimizations_Ace_Mc_Admin_Page( $settings ?? new Optimizations_Ace_Mc_Settings() );
		$page->add_admin_menu();
		$page->register_settings();

		return $page;
	}

	/**
	 * The page hooks into the menu, the settings registration, and the style queue, and nothing else.
	 */
	public function test_register_hooks_adds_the_three_admin_actions(): void {
		$page = new Optimizations_Ace_Mc_Admin_Page( new Optimizations_Ace_Mc_Settings() );
		$page->register_hooks();

		$this->assertSame( array( 'admin_enqueue_scripts', 'admin_init', 'admin_menu' ), $this->hooked_names() );
		$this->assertSame( 10, has_action( 'admin_menu', array( $page, 'add_admin_menu' ) ) );
		$this->assertSame( 10, has_action( 'admin_init', array( $page, 'register_settings' ) ) );
		$this->assertSame( 10, has_action( 'admin_enqueue_scripts', array( $page, 'enqueue_admin_styles' ) ) );
	}

	/**
	 * The page sits under Settings, needs manage_options, and renders through render().
	 */
	public function test_menu_entry_needs_manage_options(): void {
		$page = new Optimizations_Ace_Mc_Admin_Page( new Optimizations_Ace_Mc_Settings() );
		$page->add_admin_menu();

		$this->assertCount( 1, $GLOBALS['oam_test']['options_pages'] );
		$entry = $GLOBALS['oam_test']['options_pages'][0];
		$this->assertSame( 'manage_options', $entry['capability'] );
		$this->assertSame( 'optimizations-ace-mc', $entry['menu_slug'] );
		$this->assertSame( array( $page, 'render' ), $entry['callback'] );
		$this->assertSame( 'ACE MC Optimizations', $entry['page_title'] );
		$this->assertSame( 'ACE MC Optimizations', $entry['menu_title'] );
	}

	/**
	 * WordPress prints the menu title as given, so the plugin escapes both titles.
	 */
	public function test_menu_titles_are_escaped_before_wordpress_prints_them(): void {
		$this->use_translation_with_markup();
		( new Optimizations_Ace_Mc_Admin_Page( new Optimizations_Ace_Mc_Settings() ) )->add_admin_menu();

		$entry = $GLOBALS['oam_test']['options_pages'][0];
		$this->assert_no_raw_markup( $entry['page_title'] );
		$this->assert_no_raw_markup( $entry['menu_title'] );
	}

	/**
	 * The stylesheet loads on the plugin's own screen only.
	 */
	public function test_stylesheet_loads_on_the_settings_screen_only(): void {
		$page = new Optimizations_Ace_Mc_Admin_Page( new Optimizations_Ace_Mc_Settings() );
		$page->add_admin_menu();

		foreach ( array( 'index.php', 'options-general.php', 'settings_page_another-plugin', 'users.php' ) as $other_screen ) {
			$page->enqueue_admin_styles( $other_screen );
		}
		$this->assertSame( array(), $GLOBALS['oam_test']['styles'] );

		$page->enqueue_admin_styles( 'settings_page_optimizations-ace-mc' );

		$this->assertSame( array( 'optimizations-ace-mc-admin' ), array_keys( $GLOBALS['oam_test']['styles'] ) );
		$style = $GLOBALS['oam_test']['styles']['optimizations-ace-mc-admin'];
		$this->assertStringEndsWith( '/assets/css/admin.css', $style['src'] );
		$this->assertSame( OPTIMIZATIONS_ACE_MC_VERSION, $style['version'] );
		$this->assertSame( array(), $style['deps'] );
		$this->assertSame(
			array(
				array(
					'path'   => 'assets/css/admin.css',
					'plugin' => OPTIMIZATIONS_ACE_MC_PLUGIN_FILE,
				),
			),
			$GLOBALS['oam_test']['plugins_url_calls']
		);
		$this->assertFileExists( dirname( OPTIMIZATIONS_ACE_MC_PLUGIN_FILE ) . '/assets/css/admin.css' );
	}

	/**
	 * One option is registered, with the repository's sanitizer and all-off defaults.
	 */
	public function test_one_setting_is_registered_with_the_sanitizer(): void {
		$settings = new Optimizations_Ace_Mc_Settings();
		( new Optimizations_Ace_Mc_Admin_Page( $settings ) )->register_settings();

		$this->assertCount( 1, $GLOBALS['oam_test']['registered_settings'] );
		$registered = $GLOBALS['oam_test']['registered_settings'][0];
		$this->assertSame( 'optimizations_ace_mc_group', $registered['group'] );
		$this->assertSame( 'optimizations_ace_mc_settings', $registered['name'] );
		$this->assertSame( array( $settings, 'sanitize_settings' ), $registered['args']['sanitize_callback'] );
		$this->assertSame( 'array', $registered['args']['type'] );
		$this->assertSame( $settings->defaults(), $registered['args']['default'] );
		$this->assertSame( array(), array_filter( $registered['args']['default'] ) );
	}

	/**
	 * Three sections and six fields are registered on the plugin's page, each field in its section.
	 */
	public function test_sections_and_fields_are_registered_on_the_page(): void {
		$page = $this->registered_page();

		$this->assertSame(
			array( 'woocommerce_section', 'wpsl_section', 'admin_section' ),
			array_column( $GLOBALS['oam_test']['settings_sections'], 'id' )
		);
		foreach ( $GLOBALS['oam_test']['settings_sections'] as $section ) {
			$this->assertSame( 'optimizations-ace-mc', $section['page'] );
			$this->assertSame( array( $page, $section['id'] . '_callback' ), $section['callback'] );
			$this->assertNotSame( '', $section['title'] );
		}

		$fields = $GLOBALS['oam_test']['settings_fields'];
		$this->assertSame( self::FIELDS, array_column( $fields, 'section', 'id' ) );
		foreach ( $fields as $field ) {
			$this->assertSame( 'optimizations-ace-mc', $field['page'] );
			$this->assertSame( array( $page, 'checkbox_field_callback' ), $field['callback'] );
			$this->assertSame( array( 'label_for' => $field['id'] ), $field['args'], 'WordPress turns label_for into the label of the field.' );
			$this->assertNotSame( '', $field['title'] );
		}
		$this->assertCount( 6, array_unique( array_column( $fields, 'title' ) ) );
	}

	/**
	 * WordPress prints section and field titles as given, so the plugin escapes them.
	 */
	public function test_section_and_field_titles_are_escaped_before_wordpress_prints_them(): void {
		$this->use_translation_with_markup();
		$this->registered_page();

		foreach ( array_merge( $GLOBALS['oam_test']['settings_sections'], $GLOBALS['oam_test']['settings_fields'] ) as $entry ) {
			$this->assert_no_raw_markup( $entry['title'] );
		}
	}

	/**
	 * A checkbox carries the option name, its own ID, and a link to its description.
	 */
	public function test_checkbox_field_prints_one_described_checkbox(): void {
		$page  = $this->registered_page();
		$html  = $this->output_of( static fn() => $page->checkbox_field_callback( array( 'label_for' => 'wpsl_disable_rest_api' ) ) );
		$xpath = $this->dom( $html );

		$inputs = $xpath->query( '//input' );
		$this->assertSame( 1, $inputs->length );
		$input = $inputs->item( 0 );
		$this->assertSame( 'checkbox', $input->getAttribute( 'type' ) );
		$this->assertSame( 'wpsl_disable_rest_api', $input->getAttribute( 'id' ) );
		$this->assertSame( 'optimizations_ace_mc_settings[wpsl_disable_rest_api]', $input->getAttribute( 'name' ) );
		$this->assertSame( '1', $input->getAttribute( 'value' ) );
		$this->assertFalse( $input->hasAttribute( 'checked' ) );

		$described_by = $input->getAttribute( 'aria-describedby' );
		$this->assertSame( 'wpsl_disable_rest_api-description', $described_by );
		$description = $xpath->query( '//*[@id="' . $described_by . '"]' );
		$this->assertSame( 1, $description->length );
		$this->assertSame( ( new Optimizations_Ace_Mc_Settings() )->get_field_description( 'wpsl_disable_rest_api' ), trim( $description->item( 0 )->textContent ) );
		$this->assertSame( 0, $xpath->query( '//label' )->length, 'The label comes from WordPress (label_for); a second one would double it.' );
	}

	/**
	 * The checkbox is checked exactly when its setting is on.
	 */
	public function test_checkbox_reflects_the_stored_setting(): void {
		$page = $this->registered_page( $this->settings_enabled( 'woocommerce_hide_category_count' ) );

		foreach ( array_keys( self::FIELDS ) as $name ) {
			$xpath = $this->dom( $this->output_of( static fn() => $page->checkbox_field_callback( array( 'label_for' => $name ) ) ) );
			$this->assertSame( 'woocommerce_hide_category_count' === $name, $xpath->query( '//input' )->item( 0 )->hasAttribute( 'checked' ), $name );
		}
	}

	/**
	 * A field that is not one of the plugin's settings prints nothing.
	 */
	public function test_checkbox_field_prints_nothing_for_an_unknown_field(): void {
		$page = $this->registered_page();

		foreach ( array( array(), array( 'label_for' => '' ), array( 'label_for' => 'unknown_option' ), array( 'label_for' => 'wpsl_disable_rest_api"><script>' ) ) as $arguments ) {
			$this->assertSame( '', $this->output_of( static fn() => $page->checkbox_field_callback( $arguments ) ) );
		}
	}

	/**
	 * A description that holds markup is printed as text.
	 */
	public function test_checkbox_description_is_escaped(): void {
		$this->use_translation_with_markup();
		$page = $this->registered_page();

		$html = $this->output_of( static fn() => $page->checkbox_field_callback( array( 'label_for' => 'wpsl_show_store_categories' ) ) );

		$this->assert_no_raw_markup( $html );
	}

	/**
	 * Section introductions are printed as paragraphs of text.
	 */
	public function test_section_introductions_are_escaped(): void {
		$this->use_translation_with_markup();
		$page = $this->registered_page();

		foreach ( array( 'woocommerce_section_callback', 'wpsl_section_callback', 'admin_section_callback' ) as $method ) {
			$html = $this->output_of( array( $page, $method ) );
			$this->assertStringStartsWith( '<p>', $html );
			$this->assert_no_raw_markup( $html );
		}
	}

	/**
	 * Without manage_options the page refuses and prints nothing.
	 */
	public function test_render_refuses_a_user_without_manage_options(): void {
		$GLOBALS['oam_test']['current_user_can'] = false;
		$page                                    = $this->registered_page();
		$refused                                 = false;

		ob_start();
		try {
			$page->render();
		} catch ( Oam_Test_Die_Exception $exception ) {
			$refused = true;
			$this->assertNotSame( '', $exception->getMessage() );
		} finally {
			$html = (string) ob_get_clean();
		}

		$this->assertTrue( $refused, 'render() must stop the request.' );
		$this->assertSame( '', $html );
		$this->assertSame( array( 'manage_options' ), $GLOBALS['oam_test']['capability_checks'] );
	}

	/**
	 * The page posts the six checkboxes of the plugin's option group to options.php.
	 */
	public function test_render_prints_the_settings_form(): void {
		$page  = $this->registered_page();
		$xpath = $this->dom( $this->output_of( array( $page, 'render' ) ) );

		$this->assertSame( array( 'manage_options' ), $GLOBALS['oam_test']['capability_checks'] );
		$this->assertSame( 1, $xpath->query( '//div[contains(concat(" ", normalize-space(@class), " "), " wrap ")]/h1' )->length );

		$forms = $xpath->query( '//form' );
		$this->assertSame( 1, $forms->length );
		$this->assertSame( 'post', strtolower( $forms->item( 0 )->getAttribute( 'method' ) ) );
		$this->assertSame( 'options.php', $forms->item( 0 )->getAttribute( 'action' ) );
		$this->assertSame( 'optimizations_ace_mc_group', $xpath->query( '//form//input[@name="option_page"]' )->item( 0 )->getAttribute( 'value' ) );

		$names = array();
		foreach ( $xpath->query( '//form//input[@type="checkbox"]' ) as $checkbox ) {
			$names[] = $checkbox->getAttribute( 'name' );
		}
		$this->assertSame(
			array_map( static fn( string $name ): string => 'optimizations_ace_mc_settings[' . $name . ']', array_keys( self::FIELDS ) ),
			$names
		);
		$this->assertSame( 1, $xpath->query( '//form//input[@type="submit"]' )->length );
	}

	/**
	 * Every ID on the page is unique, and every aria-describedby names an element on the page.
	 */
	public function test_render_uses_unique_ids_and_valid_references(): void {
		$xpath = $this->dom( $this->output_of( array( $this->registered_page(), 'render' ) ) );

		$ids = array();
		foreach ( $xpath->query( '//*[@id and @id != "oam-test-root"]' ) as $element ) {
			$ids[] = $element->getAttribute( 'id' );
		}
		$this->assertSame( array_unique( $ids ), $ids );

		$references = $xpath->query( '//*[@aria-describedby]' );
		$this->assertSame( 6, $references->length );
		foreach ( $references as $element ) {
			$this->assertContains( $element->getAttribute( 'aria-describedby' ), $ids );
		}
	}

	/**
	 * The information box stays in place (inline) and shows the plugin version.
	 */
	public function test_render_shows_the_information_box_and_version(): void {
		$xpath = $this->dom( $this->output_of( array( $this->registered_page(), 'render' ) ) );

		$boxes = $xpath->query( '//div[contains(concat(" ", normalize-space(@class), " "), " notice-info ")]' );
		$this->assertSame( 1, $boxes->length );
		$classes = preg_split( '/\s+/', trim( $boxes->item( 0 )->getAttribute( 'class' ) ) );
		$this->assertContains( 'notice', $classes );
		$this->assertContains( 'inline', $classes, 'Without "inline", WordPress moves the box to the top of the page.' );
		$this->assertStringContainsString( OPTIMIZATIONS_ACE_MC_VERSION, $boxes->item( 0 )->textContent );
	}

	/**
	 * The link that opens a new tab says so and does not hand the opener to the other site.
	 */
	public function test_render_marks_the_link_that_opens_a_new_tab(): void {
		$xpath = $this->dom( $this->output_of( array( $this->registered_page(), 'render' ) ) );

		$links = $xpath->query( '//a[@target="_blank"]' );
		$this->assertSame( 1, $links->length );
		$link = $links->item( 0 );
		$this->assertStringStartsWith( 'https://', $link->getAttribute( 'href' ) );
		$rel = preg_split( '/\s+/', trim( $link->getAttribute( 'rel' ) ) );
		$this->assertContains( 'noopener', $rel );
		$this->assertContains( 'noreferrer', $rel );
		$this->assertSame( 1, $xpath->query( './/span[@class="screen-reader-text"]', $link )->length );
		$this->assertNotSame( '', trim( $xpath->query( './/span[@class="screen-reader-text"]', $link )->item( 0 )->textContent ) );
	}

	/**
	 * Markup in a translation or in the stored page title is printed as text everywhere on the page.
	 */
	public function test_render_prints_translations_and_the_page_title_as_text(): void {
		$this->use_translation_with_markup();
		$GLOBALS['oam_test']['page_title'] = 'Title' . self::MARKUP;
		$html                              = $this->output_of( array( $this->registered_page(), 'render' ) );

		$this->assert_no_raw_markup( $html );
		$this->assertSame( 0, $this->dom( $html )->query( '//script' )->length );
	}

	/**
	 * Every selector in the stylesheet matches an element of the page the plugin prints.
	 */
	public function test_every_stylesheet_selector_matches_the_page(): void {
		$css = (string) file_get_contents( dirname( OPTIMIZATIONS_ACE_MC_PLUGIN_FILE ) . '/assets/css/admin.css' );
		$css = (string) preg_replace( '#/\*.*?\*/#s', '', $css );
		preg_match_all( '/([^{}]+)\{[^{}]*\}/', $css, $rules );
		$xpath = $this->dom( $this->output_of( array( $this->registered_page(), 'render' ) ) );

		$selectors = array();
		foreach ( $rules[1] as $selector_list ) {
			foreach ( explode( ',', $selector_list ) as $selector ) {
				$selectors[] = trim( $selector );
			}
		}

		$this->assertNotSame( array(), $selectors, 'The stylesheet has no rules.' );
		foreach ( $selectors as $selector ) {
			$this->assertGreaterThan( 0, $xpath->query( $this->selector_to_xpath( $selector ) )->length, sprintf( 'Nothing on the page matches "%s".', $selector ) );
		}
	}

	/**
	 * Translate a selector made of class names, element names, and descendant
	 * combinators. Anything else fails, so that a new kind of selector cannot
	 * pass unchecked.
	 *
	 * @param string $selector CSS selector.
	 */
	private function selector_to_xpath( string $selector ): string {
		$xpath = '';

		foreach ( preg_split( '/\s+/', $selector ) as $part ) {
			if ( 1 !== preg_match( '/\A([a-z][a-z0-9]*)?((?:\.[A-Za-z0-9_-]+)*)\z/', $part, $matches ) || '' === $part ) {
				$this->fail( sprintf( 'The selector "%s" needs a contract check that this test does not have.', $selector ) );
			}

			$xpath .= '//' . ( '' === $matches[1] ? '*' : $matches[1] );
			foreach ( array_filter( explode( '.', $matches[2] ) ) as $class ) {
				$xpath .= '[contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")]';
			}
		}

		return $xpath;
	}
}
