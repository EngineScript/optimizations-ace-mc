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

		// Settings are saved in the admin. WP Store Locator caches the store data
		// that the category text is part of, so drop that cache when the setting changes.
		if ( is_admin() ) {
			add_action( 'update_option_' . Optimizations_Ace_Mc_Settings::OPTION_NAME, array( $this, 'flush_store_cache_on_change' ), 10, 2 );
			add_action( 'add_option_' . Optimizations_Ace_Mc_Settings::OPTION_NAME, array( $this, 'flush_store_cache_on_first_save' ), 10, 2 );
		}
	}

	/**
	 * Add store categories to store meta.
	 *
	 * Other plugins can filter the store meta first, so a non-array value is passed through unchanged.
	 *
	 * @since 1.0.9
	 * @param mixed $store_meta Existing store meta.
	 * @param mixed $store_id   Store ID.
	 * @return mixed
	 */
	public function add_store_categories_to_meta( mixed $store_meta, mixed $store_id = 0 ): mixed {
		if ( ! is_array( $store_meta ) ) {
			return $store_meta;
		}

		$terms               = get_the_terms( is_scalar( $store_id ) ? absint( $store_id ) : 0, 'wpsl_store_category' );
		$store_meta['terms'] = '';

		if ( false === $terms || is_wp_error( $terms ) || [] === $terms ) {
			return $store_meta;
		}

		$term_names          = array_filter( array_filter( wp_list_pluck( $terms, 'name' ) ), 'is_string' );
		$escaped_term_names  = array_map( 'esc_html', $term_names );
		$store_meta['terms'] = implode( ', ', $escaped_term_names );

		return $store_meta;
	}

	/**
	 * Add the store categories to WP Store Locator's info window template.
	 *
	 * WP Store Locator builds the template from its own settings. The categories
	 * are added to that template, in front of the action links, so everything
	 * else it shows in the info window stays as configured there.
	 *
	 * Store data that was cached before the setting was enabled has no category
	 * text yet, so the template checks that the value exists before using it.
	 *
	 * The category label defaults to "Certifications:" and can be changed via
	 * the 'optimizations_ace_mc_store_category_label' filter.
	 *
	 * @since 1.0.9
	 * @param mixed $template Info window template from WP Store Locator.
	 * @return mixed
	 */
	public function customize_info_window_template( mixed $template = '' ): mixed {
		if ( ! is_string( $template ) ) {
			return $template;
		}

		/**
		 * Filters the label shown before store categories in the WPSL info window.
		 *
		 * @since 1.0.9
		 * @param string $label The category label. Default 'Certifications:'.
		 */
		$category_label = apply_filters( 'optimizations_ace_mc_store_category_label', __( 'Certifications:', 'optimizations-ace-mc' ) );

		// The category text is escaped where it is built, in add_store_categories_to_meta(),
		// so the template prints it as it is, the way WP Store Locator prints its own markup.
		// phpcs:ignore WordPressVIPMinimum.Security.Underscorejs.OutputNotation -- Escaped in add_store_categories_to_meta().
		$category_text = '<%= terms %>';

		$categories = "\t" . '<% if ( typeof terms !== "undefined" && terms ) { %>' . "\r\n"
			. "\t" . '<p>' . esc_html( $category_label ) . ' ' . $category_text . '</p>' . "\r\n"
			. "\t" . '<% } %>' . "\r\n";

		foreach ( array( '<%= createInfoWindowActions(', '</div>' ) as $anchor ) {
			$position = strrpos( $template, $anchor );

			if ( false !== $position ) {
				// Insert in front of the anchor's indentation, so that the anchor keeps it.
				$position = strlen( rtrim( substr( $template, 0, $position ), " \t" ) );

				return substr( $template, 0, $position ) . $categories . substr( $template, $position );
			}
		}

		return $template . $categories;
	}

	/**
	 * Drop WP Store Locator's cached store data when the category setting changes.
	 *
	 * @since 1.6.1
	 * @param mixed $old_value Settings before the save.
	 * @param mixed $value     Settings after the save.
	 */
	public function flush_store_cache_on_change( mixed $old_value, mixed $value ): void {
		$was_enabled = is_array( $old_value ) && ! empty( $old_value['wpsl_show_store_categories'] );
		$is_enabled  = is_array( $value ) && ! empty( $value['wpsl_show_store_categories'] );

		// WP Store Locator is optional, and the function exists since its version 3.0.
		if ( $was_enabled !== $is_enabled && function_exists( 'wpsl_flush_store_cache' ) ) {
			wpsl_flush_store_cache();
		}
	}

	/**
	 * Drop WP Store Locator's cached store data when the settings are saved for the first time.
	 *
	 * @since 1.6.1
	 * @param mixed $option Option name.
	 * @param mixed $value  Settings after the save.
	 */
	public function flush_store_cache_on_first_save( mixed $option, mixed $value ): void {
		unset( $option );

		$this->flush_store_cache_on_change( array(), $value );
	}

	/**
	 * Disable REST API for WP Store Locator post type.
	 *
	 * Other plugins can filter these arguments first, so a non-array value is passed through unchanged.
	 *
	 * @since 1.0.9
	 * @param mixed $args Post type arguments.
	 * @return mixed
	 */
	public function disable_store_locator_rest_api( mixed $args ): mixed {
		if ( ! is_array( $args ) ) {
			return $args;
		}

		$args['show_in_rest'] = false;

		return $args;
	}
}
