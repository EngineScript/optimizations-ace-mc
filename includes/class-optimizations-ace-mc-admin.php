<?php
/**
 * Admin functionality.
 *
 * @package OptimizationsAceMc
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin class.
 */
class Optimizations_Ace_Mc_Admin {

	/**
	 * The single instance of the class.
	 *
	 * @var Optimizations_Ace_Mc_Admin
	 */
	protected static $instance = null;

	/**
	 * Main instance.
	 *
	 * @return Optimizations_Ace_Mc_Admin
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
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_filter( 'plugin_action_links_' . OPTIMIZATIONS_ACE_MC_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Add admin menu.
	 */
	public function admin_menu() {
		add_options_page(
			__( 'Optimizations ACE MC', 'optimizations-ace-mc' ),
			__( 'Optimizations ACE MC', 'optimizations-ace-mc' ),
			'manage_options',
			'optimizations-ace-mc',
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Initialize admin settings.
	 */
	public function admin_init() {
		register_setting(
			'optimizations_ace_mc_options',
			'optimizations_ace_mc_options',
			array( $this, 'validate_options' )
		);

		add_settings_section(
			'optimizations_ace_mc_main',
			__( 'Main Settings', 'optimizations-ace-mc' ),
			array( $this, 'main_section_callback' ),
			'optimizations-ace-mc'
		);

		add_settings_field(
			'enable_optimizations',
			__( 'Enable Optimizations', 'optimizations-ace-mc' ),
			array( $this, 'enable_optimizations_callback' ),
			'optimizations-ace-mc',
			'optimizations_ace_mc_main'
		);
	}

	/**
	 * Admin page callback.
	 */
	public function admin_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'optimizations_ace_mc_options' );
				do_settings_sections( 'optimizations-ace-mc' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Main section callback.
	 */
	public function main_section_callback() {
		echo '<p>' . esc_html__( 'Configure your optimization settings below.', 'optimizations-ace-mc' ) . '</p>';
	}

	/**
	 * Enable optimizations field callback.
	 */
	public function enable_optimizations_callback() {
		$options = get_option( 'optimizations_ace_mc_options', array() );
		$enabled = isset( $options['enable_optimizations'] ) ? $options['enable_optimizations'] : true;
		?>
		<input type="checkbox" name="optimizations_ace_mc_options[enable_optimizations]" value="1" <?php checked( $enabled, true ); ?> />
		<label for="optimizations_ace_mc_options[enable_optimizations]">
			<?php esc_html_e( 'Enable optimization features', 'optimizations-ace-mc' ); ?>
		</label>
		<?php
	}

	/**
	 * Validate options.
	 *
	 * @param array $input Input options.
	 * @return array
	 */
	public function validate_options( $input ) {
		$output = array();
		
		if ( isset( $input['enable_optimizations'] ) ) {
			$output['enable_optimizations'] = (bool) $input['enable_optimizations'];
		}
		
		return $output;
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Plugin action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=optimizations-ace-mc' ) . '">' . __( 'Settings', 'optimizations-ace-mc' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
}
