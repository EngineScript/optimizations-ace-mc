<?php
/**
 * Main plugin coordinator.
 *
 * @package OptimizationsAceMc
 * @since   1.0.9
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main plugin coordinator.
 *
 * @since 1.0.9
 */
final class Optimizations_Ace_Mc {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Settings repository.
	 *
	 * @since 1.0.9
	 * @var Optimizations_Ace_Mc_Settings
	 */
	private Optimizations_Ace_Mc_Settings $settings;

	/**
	 * Whether plugin hooks have been registered.
	 *
	 * @since 1.0.9
	 * @var bool
	 */
	private bool $hooks_registered = false;

	/**
	 * Main instance.
	 *
	 * @since 1.0.0
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Prevent cloning.
	 *
	 * @since 1.0.7
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing.
	 *
	 * @since 1.0.7
	 * @throws LogicException Always; the coordinator must not be recreated from serialized data.
	 */
	public function __wakeup(): void {
		throw new LogicException( esc_html__( 'Unserializing is not allowed.', 'optimizations-ace-mc' ) );
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->settings = new Optimizations_Ace_Mc_Settings();
	}

	/**
	 * Register plugin hooks.
	 *
	 * @since 1.0.9
	 */
	public function register_hooks(): void {
		if ( $this->hooks_registered ) {
			return;
		}

		$this->hooks_registered = true;

		( new Optimizations_Ace_Mc_Admin_Page( $this->settings ) )->register_hooks();
		( new Optimizations_Ace_Mc_WooCommerce_Optimizations( $this->settings ) )->register_hooks();
		( new Optimizations_Ace_Mc_Wpsl_Optimizations( $this->settings ) )->register_hooks();
		( new Optimizations_Ace_Mc_Admin_Optimizations( $this->settings ) )->register_hooks();
	}
}
