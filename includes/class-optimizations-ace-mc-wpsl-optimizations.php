<?php
/**
 * WP Store Locator optimizations.
 *
 * @package OptimizationsAceMc
 * @since   1.0.9
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * WP Store Locator optimizations.
 *
 * @since 1.0.9
 */
final class Optimizations_Ace_Mc_Wpsl_Optimizations {

	/**
	 * Settings repository.
	 *
	 * @since 1.0.9
	 * @var Optimizations_Ace_Mc_Settings
	 */
	private Optimizations_Ace_Mc_Settings $settings;

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
	 * Register WP Store Locator hooks.
	 *
	 * @since 1.0.9
	 */
	public function register_hooks(): void {
		if ( $this->settings->is_enabled( 'wpsl_show_store_categories' ) ) {
			add_filter( 'wpsl_store_meta', array( $this, 'add_store_categories_to_meta' ), 10, 2 );
			add_filter( 'wpsl_info_window_template', array( $this, 'customize_info_window_template' ) );
		}

		if ( $this->settings->is_enabled( 'wpsl_disable_rest_api' ) ) {
			add_filter( 'wpsl_post_type_args', array( $this, 'disable_store_locator_rest_api' ) );
		}
	}

	/**
	 * Add store categories to store meta.
	 *
	 * @since 1.0.9
	 * @param array<string, mixed> $store_meta Existing store meta.
	 * @param int                  $store_id Store ID.
	 * @return array<string, mixed>
	 */
	public function add_store_categories_to_meta( array $store_meta, int $store_id ): array {
		$terms               = get_the_terms( absint( $store_id ), 'wpsl_store_category' );
		$store_meta['terms'] = '';

		if ( false === $terms || is_wp_error( $terms ) || [] === $terms ) {
			return $store_meta;
		}

		$term_names          = array_filter( wp_list_pluck( $terms, 'name' ) );
		$escaped_term_names  = array_map( 'esc_html', $term_names );
		$store_meta['terms'] = implode( ', ', $escaped_term_names );

		return $store_meta;
	}

	/**
	 * Customize info window template to include categories.
	 *
	 * The category label defaults to "Certifications:" and can be changed via
	 * the 'optimizations_ace_mc_store_category_label' filter.
	 *
	 * @since 1.0.9
	 * @return string
	 */
	public function customize_info_window_template(): string {
		/**
		 * Filters the label shown before store categories in the WPSL info window.
		 *
		 * @since 1.0.9
		 * @param string $label The category label. Default 'Certifications:'.
		 */
		$category_label = apply_filters( 'optimizations_ace_mc_store_category_label', __( 'Certifications:', 'optimizations-ace-mc' ) );

		return sprintf(
			'<div data-store-id="<%%= id %%>" class="wpsl-info-window">
				<p>
					%1$s
					<span><%%= address %%></span>
					<%% if ( address2 ) { %%>
					<span><%%= address2 %%></span>
					<%% } %%>
					<span>%2$s</span>
				</p>
				<%% if ( terms ) { %%>
				<p>%3$s <%%= terms %%></p>
				<%% } %%>
				<%%= createInfoWindowActions( id ) %%>
			</div>',
			wpsl_store_header_template(),
			wpsl_address_format_placeholders(),
			esc_html( $category_label )
		);
	}

	/**
	 * Disable REST API for WP Store Locator post type.
	 *
	 * @since 1.0.9
	 * @param array<string, mixed> $args Post type arguments.
	 * @return array<string, mixed>
	 */
	public function disable_store_locator_rest_api( array $args ): array {
		$args['show_in_rest'] = false;

		return $args;
	}
}
