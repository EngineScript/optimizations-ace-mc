<?php
/**
 * Core plugin functionality.
 *
 * @package OptimizationsAceMc
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Core plugin class.
 */
class Optimizations_Ace_Mc_Core {

	/**
	 * The single instance of the class.
	 *
	 * @var Optimizations_Ace_Mc_Core
	 */
	protected static $instance = null;

	/**
	 * Main instance.
	 *
	 * @return Optimizations_Ace_Mc_Core
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Initialize core functionality.
	 */
	public function init() {
		// Add any initialization code here.
	}

	/**
	 * Enqueue frontend scripts and styles.
	 */
	public function enqueue_scripts() {
		// Enqueue frontend assets if needed.
	}

	/**
	 * Enqueue admin scripts and styles.
	 */
	public function admin_enqueue_scripts() {
		// Enqueue admin assets if needed.
	}

	/**
	 * Get plugin options.
	 *
	 * @return array
	 */
	public function get_options() {
		return get_option( 'optimizations_ace_mc_options', array() );
	}

	/**
	 * Update plugin options.
	 *
	 * @param array $options Plugin options.
	 */
	public function update_options( $options ) {
		update_option( 'optimizations_ace_mc_options', $options );
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return OPTIMIZATIONS_ACE_MC_VERSION;
	}
}
