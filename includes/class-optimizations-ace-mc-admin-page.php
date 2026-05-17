<?php
/**
 * Settings page UI.
 *
 * @package OptimizationsAceMc
 * @since   1.0.9
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Settings page UI.
 *
 * @since 1.0.9
 */
final class Optimizations_Ace_Mc_Admin_Page {

	/**
	 * Settings repository.
	 *
	 * @since 1.0.9
	 * @var Optimizations_Ace_Mc_Settings
	 */
	private Optimizations_Ace_Mc_Settings $settings;

	/**
	 * Settings page hook suffix for conditional asset loading.
	 *
	 * @since 1.0.9
	 * @var string
	 */
	private string $settings_page_hook = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.9
	 * @param Optimizations_Ace_Mc_Settings $settings Settings repository.
	 */
	public function __construct( Optimizations_Ace_Mc_Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Register admin page hooks.
	 *
	 * @since 1.0.9
	 */
	public function register_hooks(): void {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
	}

	/**
	 * Add admin menu.
	 *
	 * @since 1.0.9
	 */
	public function add_admin_menu(): void {
		$this->settings_page_hook = (string) add_options_page(
			__( 'ACE MC Optimizations', 'optimizations-ace-mc' ),
			__( 'ACE MC Optimizations', 'optimizations-ace-mc' ),
			'manage_options',
			Optimizations_Ace_Mc_Settings::PAGE_SLUG,
			array( $this, 'render' )
		);
	}

	/**
	 * Enqueue admin styles on the plugin settings page only.
	 *
	 * @since 1.0.9
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_admin_styles( string $hook_suffix ): void {
		if ( $hook_suffix !== $this->settings_page_hook ) {
			return;
		}

		wp_enqueue_style(
			'optimizations-ace-mc-admin',
			OPTIMIZATIONS_ACE_MC_PLUGIN_URL . 'assets/css/admin.css',
			[],
			OPTIMIZATIONS_ACE_MC_VERSION
		);
	}

	/**
	 * Register settings, sections, and fields.
	 *
	 * @since 1.0.9
	 */
	public function register_settings(): void {
		register_setting(
			Optimizations_Ace_Mc_Settings::OPTION_GROUP,
			Optimizations_Ace_Mc_Settings::OPTION_NAME,
			[
				'type'              => 'array',
				'sanitize_callback' => array( $this->settings, 'sanitize_settings' ),
				'default'           => $this->settings->defaults(),
			]
		);

		$this->register_woocommerce_settings();
		$this->register_wpsl_settings();
		$this->register_admin_settings();
	}

	/**
	 * Register WooCommerce settings section.
	 *
	 * @since 1.0.9
	 */
	private function register_woocommerce_settings(): void {
		add_settings_section(
			'woocommerce_section',
			__( 'WooCommerce Optimizations', 'optimizations-ace-mc' ),
			array( $this, 'woocommerce_section_callback' ),
			Optimizations_Ace_Mc_Settings::PAGE_SLUG
		);

		$this->add_checkbox_field( 'woocommerce_show_empty_categories', __( 'Show Empty Categories', 'optimizations-ace-mc' ), 'woocommerce_section' );
		$this->add_checkbox_field( 'woocommerce_hide_category_count', __( 'Hide Category Product Count', 'optimizations-ace-mc' ), 'woocommerce_section' );
		$this->add_checkbox_field( 'woocommerce_user_order_count_column', __( 'User Order Count Column', 'optimizations-ace-mc' ), 'woocommerce_section' );
	}

	/**
	 * Register WP Store Locator settings section.
	 *
	 * @since 1.0.9
	 */
	private function register_wpsl_settings(): void {
		add_settings_section(
			'wpsl_section',
			__( 'WP Store Locator Optimizations', 'optimizations-ace-mc' ),
			array( $this, 'wpsl_section_callback' ),
			Optimizations_Ace_Mc_Settings::PAGE_SLUG
		);

		$this->add_checkbox_field( 'wpsl_show_store_categories', __( 'Show Store Categories', 'optimizations-ace-mc' ), 'wpsl_section' );
		$this->add_checkbox_field( 'wpsl_disable_rest_api', __( 'Disable REST API', 'optimizations-ace-mc' ), 'wpsl_section' );
	}

	/**
	 * Register WordPress admin settings section.
	 *
	 * @since 1.0.9
	 */
	private function register_admin_settings(): void {
		add_settings_section(
			'admin_section',
			__( 'WordPress Admin Optimizations', 'optimizations-ace-mc' ),
			array( $this, 'admin_section_callback' ),
			Optimizations_Ace_Mc_Settings::PAGE_SLUG
		);

		$this->add_checkbox_field( 'admin_user_registration_date_column', __( 'User Registration Date Column', 'optimizations-ace-mc' ), 'admin_section' );
	}

	/**
	 * Register a checkbox settings field.
	 *
	 * @since 1.0.9
	 * @param string $name Field name.
	 * @param string $title Field title.
	 * @param string $section Section ID.
	 */
	private function add_checkbox_field( string $name, string $title, string $section ): void {
		add_settings_field(
			$name,
			$title,
			array( $this, 'checkbox_field_callback' ),
			Optimizations_Ace_Mc_Settings::PAGE_SLUG,
			$section,
			[
				'label_for' => $name,
			]
		);
	}

	/**
	 * WooCommerce section callback.
	 *
	 * @since 1.0.9
	 */
	public function woocommerce_section_callback(): void {
		echo '<p>' . esc_html__( 'Configure WooCommerce-specific optimizations. These features enhance the WooCommerce experience and admin functionality.', 'optimizations-ace-mc' ) . '</p>';
	}

	/**
	 * WP Store Locator section callback.
	 *
	 * @since 1.0.9
	 */
	public function wpsl_section_callback(): void {
		echo '<p>' . esc_html__( 'Configure WP Store Locator optimizations. These features enhance store locator functionality and security.', 'optimizations-ace-mc' ) . '</p>';
	}

	/**
	 * WordPress Admin section callback.
	 *
	 * @since 1.0.9
	 */
	public function admin_section_callback(): void {
		echo '<p>' . esc_html__( 'Configure WordPress admin interface optimizations. These features enhance the admin experience.', 'optimizations-ace-mc' ) . '</p>';
	}

	/**
	 * Checkbox field callback.
	 *
	 * @since 1.0.9
	 * @param array{label_for?:string} $args Field arguments.
	 */
	public function checkbox_field_callback( array $args ): void {
		$name = $args['label_for'] ?? '';
		if ( '' === $name || ! $this->settings->has( $name ) ) {
			return;
		}

		$description = $this->settings->get_field_description( $name );
		$checked     = $this->settings->is_enabled( $name );

		printf(
			'<label for="%1$s">
				<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1" %3$s />
				%4$s
			</label>',
			esc_attr( $name ),
			esc_attr( Optimizations_Ace_Mc_Settings::OPTION_NAME ),
			checked( $checked, true, false ),
			esc_html( $description )
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @since 1.0.9
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'optimizations-ace-mc' ) );
		}

		?>
		<div class="wrap optimizations-ace-mc-wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php settings_errors(); ?>
			<?php $this->display_plugin_info(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( Optimizations_Ace_Mc_Settings::OPTION_GROUP );
				do_settings_sections( Optimizations_Ace_Mc_Settings::PAGE_SLUG );
				submit_button();
				?>
			</form>

			<?php $this->display_support_info(); ?>
		</div>
		<?php
	}

	/**
	 * Display plugin information.
	 *
	 * @since 1.0.9
	 */
	private function display_plugin_info(): void {
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'Plugin Information:', 'optimizations-ace-mc' ); ?></strong>
				<?php esc_html_e( 'This plugin provides configurable optimizations for WooCommerce, WP Store Locator, and WordPress admin interfaces.', 'optimizations-ace-mc' ); ?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Version:', 'optimizations-ace-mc' ); ?></strong> <?php echo esc_html( OPTIMIZATIONS_ACE_MC_VERSION ); ?> |
				<strong><?php esc_html_e( 'WordPress:', 'optimizations-ace-mc' ); ?></strong> <?php esc_html_e( '6.8+ required', 'optimizations-ace-mc' ); ?> |
				<strong><?php esc_html_e( 'PHP:', 'optimizations-ace-mc' ); ?></strong> <?php esc_html_e( '8.2+ required', 'optimizations-ace-mc' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Display support information.
	 *
	 * @since 1.0.9
	 */
	private function display_support_info(): void {
		?>
		<div class="optimizations-ace-mc-card">
			<h2><?php esc_html_e( 'Support & Documentation', 'optimizations-ace-mc' ); ?></h2>
			<p>
				<?php esc_html_e( 'For support, bug reports, or feature requests:', 'optimizations-ace-mc' ); ?>
				<a href="https://github.com/EngineScript/optimizations-ace-mc" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Visit the GitHub repository', 'optimizations-ace-mc' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
