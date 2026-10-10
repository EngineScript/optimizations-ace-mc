<?php
/**
 * Plugin file and coordinator tests.
 *
 * @package OptimizationsAceMc
 */

/**
 * Tests for what loading the plugin registers, and for the coordinator.
 *
 * The branch of the plugin file that skips the uninstall registration while
 * WordPress is uninstalling the plugin is left to the integration suite: the
 * file cannot be loaded a second time in one process.
 */
final class PluginWiringTest extends Oam_Test_Case {

	/**
	 * Put the coordinator back to the beginning of a request: not started, and
	 * holding the settings that the request would have read.
	 *
	 * The coordinator lives for the whole test process, so its private state is
	 * set here instead of loading the plugin again.
	 *
	 * @param Optimizations_Ace_Mc_Settings|null $settings Settings the request read.
	 */
	private function coordinator_before_start( ?Optimizations_Ace_Mc_Settings $settings = null ): Optimizations_Ace_Mc {
		$coordinator = Optimizations_Ace_Mc::instance();
		( new ReflectionProperty( Optimizations_Ace_Mc::class, 'hooks_registered' ) )->setValue( $coordinator, false );

		if ( null !== $settings ) {
			( new ReflectionProperty( Optimizations_Ace_Mc::class, 'settings' ) )->setValue( $coordinator, $settings );
		}

		return $coordinator;
	}

	/**
	 * The plugin file defines its version and its own path.
	 */
	public function test_plugin_file_defines_its_constants(): void {
		$this->assertMatchesRegularExpression( '/\A\d+\.\d+\.\d+\z/', OPTIMIZATIONS_ACE_MC_VERSION );
		$this->assertSame( 'optimizations-ace-mc.php', basename( OPTIMIZATIONS_ACE_MC_PLUGIN_FILE ) );
		$this->assertFileExists( OPTIMIZATIONS_ACE_MC_PLUGIN_FILE );
	}

	/**
	 * The version constant and the plugin header name the same version.
	 */
	public function test_version_constant_matches_the_plugin_header(): void {
		$header = (string) file_get_contents( OPTIMIZATIONS_ACE_MC_PLUGIN_FILE, false, null, 0, 2048 );

		$this->assertSame( 1, preg_match( '/^ \* Version:\s*(\S+)\s*$/m', $header, $matches ) );
		$this->assertSame( OPTIMIZATIONS_ACE_MC_VERSION, $matches[1] );
	}

	/**
	 * WordPress enforces WooCommerce as a dependency. WP Store Locator is optional,
	 * because not every site that runs this plugin uses it.
	 */
	public function test_plugin_header_requires_only_woocommerce(): void {
		$header = (string) file_get_contents( OPTIMIZATIONS_ACE_MC_PLUGIN_FILE, false, null, 0, 2048 );

		$this->assertSame( 1, preg_match( '/^ \* Requires Plugins:\s*(.*?)\s*$/m', $header, $matches ) );
		$this->assertSame( 'woocommerce', $matches[1] );
	}

	/**
	 * Loading the plugin file registers the start-up action and nothing else.
	 */
	public function test_loading_the_plugin_registers_only_the_start_up_action(): void {
		$hooks = $GLOBALS['oam_test_load']['hooks'];

		$this->assertSame( array( 'plugins_loaded' ), array_keys( $hooks ) );
		$this->assertSame( 'optimizations_ace_mc_init', $hooks['plugins_loaded'][10][0]['callback'] );
		$this->assertCount( 1, $hooks['plugins_loaded'][10] );
	}

	/**
	 * Loading the plugin file registers the uninstall callback for that file.
	 */
	public function test_loading_the_plugin_registers_the_uninstall_callback(): void {
		$this->assertSame(
			array(
				array(
					'file'     => OPTIMIZATIONS_ACE_MC_PLUGIN_FILE,
					'callback' => array( 'Optimizations_Ace_Mc_Settings', 'uninstall' ),
				),
			),
			$GLOBALS['oam_test_load']['uninstall_hooks']
		);
		$this->assertTrue( is_callable( $GLOBALS['oam_test_load']['uninstall_hooks'][0]['callback'] ), 'WordPress only accepts a static callback here.' );
	}

	/**
	 * There is one coordinator, and the plugin's accessor returns it.
	 */
	public function test_there_is_one_coordinator(): void {
		$this->assertSame( Optimizations_Ace_Mc::instance(), Optimizations_Ace_Mc::instance() );
		$this->assertSame( Optimizations_Ace_Mc::instance(), optimizations_ace_mc() );
	}

	/**
	 * A second coordinator cannot be made with new, clone, or unserialize.
	 */
	public function test_a_second_coordinator_cannot_be_made(): void {
		$class = new ReflectionClass( Optimizations_Ace_Mc::class );

		$this->assertFalse( $class->isInstantiable() );
		$this->assertFalse( $class->isCloneable() );
		$this->assertTrue( $class->isFinal() );

		$this->expectException( LogicException::class );
		Optimizations_Ace_Mc::instance()->__wakeup();
	}

	/**
	 * The message of the unserialize refusal is escaped, because it can reach an error page.
	 */
	public function test_unserialize_refusal_message_is_escaped(): void {
		$this->use_translation_with_markup();

		try {
			Optimizations_Ace_Mc::instance()->__wakeup();
			$this->fail( 'Unserializing must be refused.' );
		} catch ( LogicException $exception ) {
			$this->assert_no_raw_markup( $exception->getMessage() );
		}
	}

	/**
	 * Starting the plugin registers the settings page and the listeners for a
	 * settings change; features that are off register nothing else.
	 */
	public function test_start_registers_only_the_admin_hooks_when_everything_is_off(): void {
		$this->coordinator_before_start( new Optimizations_Ace_Mc_Settings() );

		optimizations_ace_mc_init();

		$this->assertSame(
			array( 'add_option_optimizations_ace_mc_settings', 'admin_enqueue_scripts', 'admin_init', 'admin_menu', 'update_option_optimizations_ace_mc_settings' ),
			$this->hooked_names()
		);
	}

	/**
	 * Starting the plugin starts each of the four parts with the stored settings.
	 */
	public function test_start_registers_every_feature_that_is_on(): void {
		$this->coordinator_before_start(
			$this->settings_enabled( 'woocommerce_show_empty_categories', 'wpsl_disable_rest_api', 'admin_user_registration_date_column' )
		);

		optimizations_ace_mc_init();

		$this->assertSame(
			array(
				'add_option_optimizations_ace_mc_settings',
				'admin_enqueue_scripts',
				'admin_init',
				'admin_menu',
				'manage_users_columns',
				'manage_users_custom_column',
				'manage_users_sortable_columns',
				'update_option_optimizations_ace_mc_settings',
				'woocommerce_product_subcategories_hide_empty',
				'wpsl_post_type_args',
			),
			$this->hooked_names()
		);
	}

	/**
	 * Starting twice registers the hooks once.
	 */
	public function test_start_registers_hooks_once(): void {
		$coordinator = $this->coordinator_before_start();

		$coordinator->register_hooks();
		$after_first = $GLOBALS['oam_test']['hooks'];
		$coordinator->register_hooks();
		optimizations_ace_mc_init();

		$this->assertNotSame( array(), $after_first );
		$this->assertSame( $after_first, $GLOBALS['oam_test']['hooks'] );
		$this->assertCount( 1, $this->callbacks_on( 'admin_menu' ) );
	}
}
