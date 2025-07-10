<?php
/**
 * Plugin Name: Optimizations ACE MC
 * Plugin URI: https://github.com/EngineScript/Optimizations-ACE-MC
 * Description: A lightweight WordPress optimization plugin with pre-configured performance enhancements.
 * Version: 1.0.0
 * Author: EngineScript
 * Author URI: https://github.com/EngineScript
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: Optimizations-ACE-MC
 * Domain Path: /languages
 * Requires at least: 6.5
 * Tested up to: 6.8
 * Requires PHP: 7.4
 *
 * @package OptimizationsAceMc
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'OPTIMIZATIONS_ACE_MC_VERSION', '1.0.0' );
define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_FILE', __FILE__ );
define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class.
 */
class Optimizations_Ace_Mc {

	/**
	 * The single instance of the class.
	 *
	 * @var Optimizations_Ace_Mc|null
	 */
	protected static $instance = null;

	/**
	 * Main instance.
	 *
	 * @return Optimizations_Ace_Mc
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		
		// Initialize optimizations.
		$this->init_optimizations();
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'Optimizations-ACE-MC',
			false,
			dirname( OPTIMIZATIONS_ACE_MC_PLUGIN_BASENAME ) . '/languages/'
		);
	}

	/**
	 * Initialize optimization functions.
	 */
	private function init_optimizations() {
		// Optimization functions will be added here.
		// This method is ready to receive the specific code snippets.
	}
}

/**
 * Initialize the plugin.
 */
function optimizations_ace_mc() {
	return Optimizations_Ace_Mc::instance();
}

// Start the plugin.
optimizations_ace_mc();
