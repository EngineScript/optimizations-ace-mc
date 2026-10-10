<?php
/**
 * PHPUnit bootstrap for the unit suite.
 *
 * WordPress, WooCommerce, and WP Store Locator are not loaded. The functions
 * and classes below stand in for the parts of them that the plugin calls, and
 * record what the plugin asks for so that a test can check it.
 *
 * The WordPress integration job writes its own bootstrap over this file on the
 * runner, after the unit suite has run.
 *
 * @package OptimizationsAceMc
 */

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/wordpress/' );
}

// WordPress sets PHP's default time zone to UTC and the plugin relies on it,
// so the suite must not inherit the time zone of the machine it runs on.
// phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set
date_default_timezone_set( 'UTC' );

if ( class_exists( \PHPUnit\Framework\TestCase::class ) ) {
	require_once __DIR__ . '/OamTestCase.php';
}

/**
 * Minimal WP_Error, enough for is_wp_error().
 */
class WP_Error {}

/**
 * Raised by the wp_die() stand-in, so that a test can see the refusal.
 */
class Oam_Test_Die_Exception extends RuntimeException {}

/**
 * Put the recorded stand-in state back to its defaults.
 */
function oam_test_reset_state(): void {
	$GLOBALS['oam_test'] = array(
		'hooks'                => array(),
		'did_actions'          => array(),
		'uninstall_hooks'      => array(),
		'options'              => array(),
		'option_reads'         => array(),
		'deleted_options'      => array(),
		'is_admin'             => true,
		'current_user_can'     => true,
		'capability_checks'    => array(),
		'translation'          => null,
		'options_pages'        => array(),
		'registered_settings'  => array(),
		'settings_sections'    => array(),
		'settings_fields'      => array(),
		'page_title'           => 'ACE MC Optimizations',
		'styles'               => array(),
		'plugins_url_calls'    => array(),
		'users'                => array(),
		'terms'                => array(),
		'term_queries'         => array(),
		'wp_date_calls'        => array(),
		'wp_date_result'       => null,
		'number_format_calls'  => array(),
		'number_format_result' => null,
		'order_counts'         => array(),
		'order_count_calls'    => array(),
		'store_cache_flushes'  => 0,
	);
}

/**
 * Translate a string the way a test asked for, or return it unchanged.
 *
 * WordPress passes every string through the translation of the site. A test
 * can install a callable to stand for a translation file that holds markup.
 *
 * @param string $text Source text.
 */
function oam_test_translate( $text ): string {
	$translation = $GLOBALS['oam_test']['translation'];

	return is_callable( $translation ) ? (string) $translation( (string) $text ) : (string) $text;
}

/**
 * Define the WooCommerce function the plugin calls. Once defined it cannot be
 * removed, so tests that need it run in their own process.
 */
function oam_test_define_woocommerce(): void {
	if ( function_exists( 'wc_get_customer_order_count' ) ) {
		return;
	}

	/**
	 * Stand-in for WooCommerce's order count lookup.
	 *
	 * @param int $user_id User ID.
	 */
	function wc_get_customer_order_count( $user_id ) {
		$GLOBALS['oam_test']['order_count_calls'][] = $user_id;

		return $GLOBALS['oam_test']['order_counts'][ $user_id ] ?? 0;
	}
}

/**
 * Define the WP Store Locator function the plugin calls. Once defined it
 * cannot be removed, so tests that need it run in their own process.
 */
function oam_test_define_store_locator(): void {
	if ( function_exists( 'wpsl_flush_store_cache' ) ) {
		return;
	}

	/**
	 * Stand-in for WP Store Locator's cache flush.
	 */
	function wpsl_flush_store_cache() {
		++$GLOBALS['oam_test']['store_cache_flushes'];
	}
}

/*
 * Hooks.
 */

/**
 * Record a filter.
 *
 * @param string   $hook          Hook name.
 * @param callable $callback      Callback.
 * @param int      $priority      Priority.
 * @param int      $accepted_args Number of arguments.
 */
function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ): bool {
	$GLOBALS['oam_test']['hooks'][ $hook ][ $priority ][] = array(
		'callback'      => $callback,
		'accepted_args' => $accepted_args,
	);

	return true;
}

/**
 * Record an action.
 *
 * @param string   $hook          Hook name.
 * @param callable $callback      Callback.
 * @param int      $priority      Priority.
 * @param int      $accepted_args Number of arguments.
 */
function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ): bool {
	return add_filter( $hook, $callback, $priority, $accepted_args );
}

/**
 * Find a recorded filter, as WordPress does: the priority of a given
 * callback, or whether the hook has any callback.
 *
 * @param string         $hook     Hook name.
 * @param callable|false $callback Callback to look for.
 * @return int|bool
 */
function has_filter( $hook, $callback = false ) {
	$registered = $GLOBALS['oam_test']['hooks'][ $hook ] ?? array();

	if ( false === $callback ) {
		return array() !== $registered;
	}

	foreach ( $registered as $priority => $entries ) {
		foreach ( $entries as $entry ) {
			if ( $entry['callback'] === $callback ) {
				return (int) $priority;
			}
		}
	}

	return false;
}

/**
 * Find a recorded action.
 *
 * @param string         $hook     Hook name.
 * @param callable|false $callback Callback to look for.
 * @return int|bool
 */
function has_action( $hook, $callback = false ) {
	return has_filter( $hook, $callback );
}

/**
 * How often an action has run.
 *
 * @param string $hook Hook name.
 */
function did_action( $hook ): int {
	return $GLOBALS['oam_test']['did_actions'][ $hook ] ?? 0;
}

/**
 * Run the recorded callbacks of a filter in priority order.
 *
 * @param string $hook  Hook name.
 * @param mixed  $value Value to filter.
 * @param mixed  ...$arguments Further arguments.
 * @return mixed
 */
function apply_filters( $hook, $value, ...$arguments ) {
	$registered = $GLOBALS['oam_test']['hooks'][ $hook ] ?? array();
	ksort( $registered );

	foreach ( $registered as $entries ) {
		foreach ( $entries as $entry ) {
			$value = $entry['callback']( ...array_slice( array_merge( array( $value ), $arguments ), 0, (int) $entry['accepted_args'] ) );
		}
	}

	return $value;
}

/**
 * Record an uninstall callback.
 *
 * @param string   $file     Plugin file.
 * @param callable $callback Callback.
 */
function register_uninstall_hook( $file, $callback ): void {
	$GLOBALS['oam_test']['uninstall_hooks'][] = array(
		'file'     => $file,
		'callback' => $callback,
	);
}

/**
 * WordPress's callback that always returns false.
 */
function __return_false(): bool {
	return false;
}

/*
 * Translation and escaping.
 */

/**
 * Translate.
 *
 * @param string $text   Text.
 * @param string $domain Text domain.
 */
function __( $text, $domain = 'default' ): string {
	unset( $domain );

	return oam_test_translate( $text );
}

/**
 * Escape for an HTML text node. Like WordPress, existing entities are kept.
 *
 * @param mixed $text Text.
 */
function esc_html( $text ): string {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8', false );
}

/**
 * Escape for an HTML attribute.
 *
 * @param mixed $text Text.
 */
function esc_attr( $text ): string {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8', false );
}

/**
 * Translate and escape.
 *
 * @param string $text   Text.
 * @param string $domain Text domain.
 */
function esc_html__( $text, $domain = 'default' ): string {
	return esc_html( __( $text, $domain ) );
}

/**
 * Translate, escape, and print.
 *
 * @param string $text   Text.
 * @param string $domain Text domain.
 */
function esc_html_e( $text, $domain = 'default' ): void {
	echo esc_html__( $text, $domain ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/*
 * Options, users, and request context.
 */

/**
 * Read a stored option.
 *
 * @param string $name          Option name.
 * @param mixed  $default_value Value when the option is not stored.
 * @return mixed
 */
function get_option( $name, $default_value = false ) {
	$GLOBALS['oam_test']['option_reads'][] = $name;

	return array_key_exists( $name, $GLOBALS['oam_test']['options'] ) ? $GLOBALS['oam_test']['options'][ $name ] : $default_value;
}

/**
 * Record the deletion of an option.
 *
 * @param string $name Option name.
 */
function delete_option( $name ): bool {
	$GLOBALS['oam_test']['deleted_options'][] = $name;
	unset( $GLOBALS['oam_test']['options'][ $name ] );

	return true;
}

/**
 * Whether the request is for an admin screen.
 */
function is_admin(): bool {
	return (bool) $GLOBALS['oam_test']['is_admin'];
}

/**
 * Whether the current user has a capability.
 *
 * @param string $capability Capability.
 */
function current_user_can( $capability ): bool {
	$GLOBALS['oam_test']['capability_checks'][] = $capability;

	return (bool) $GLOBALS['oam_test']['current_user_can'];
}

/**
 * End the request with a message.
 *
 * @param string $message Message.
 * @throws Oam_Test_Die_Exception Always.
 */
function wp_die( $message = '' ): void {
	throw new Oam_Test_Die_Exception( (string) $message );
}

/**
 * Look up a user.
 *
 * @param int $user_id User ID.
 * @return object|false
 */
function get_userdata( $user_id ) {
	return $GLOBALS['oam_test']['users'][ $user_id ] ?? false;
}

/**
 * Format a timestamp. The stand-in does not format: it returns a marker that
 * names the format and the timestamp it was given, unless a test set a result.
 *
 * @param string $format    Date format.
 * @param int    $timestamp Timestamp.
 * @return string|false
 */
function wp_date( $format, $timestamp = null ) {
	$GLOBALS['oam_test']['wp_date_calls'][] = array(
		'format'    => $format,
		'timestamp' => $timestamp,
	);

	return $GLOBALS['oam_test']['wp_date_result'] ?? sprintf( 'date[%s|%d]', $format, $timestamp );
}

/**
 * Format a number. The stand-in returns a marker unless a test set a result.
 *
 * @param int|float $number Number.
 */
function number_format_i18n( $number ): string {
	$GLOBALS['oam_test']['number_format_calls'][] = $number;

	return $GLOBALS['oam_test']['number_format_result'] ?? sprintf( 'number[%s]', $number );
}

/**
 * Make a non-negative integer.
 *
 * @param mixed $value Value.
 */
function absint( $value ): int {
	return abs( (int) $value );
}

/*
 * Taxonomy.
 */

/**
 * Terms of a post.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy.
 * @return array<int, object>|false|WP_Error
 */
function get_the_terms( $post_id, $taxonomy ) {
	$GLOBALS['oam_test']['term_queries'][] = array(
		'post_id'  => $post_id,
		'taxonomy' => $taxonomy,
	);

	return $GLOBALS['oam_test']['terms'][ $post_id ] ?? false;
}

/**
 * Whether a value is a WP_Error.
 *
 * @param mixed $thing Value.
 */
function is_wp_error( $thing ): bool {
	return $thing instanceof WP_Error;
}

/**
 * Pluck one field from each object or array in a list, keeping the keys.
 * Like WordPress, anything but a list gives an empty result.
 *
 * @param mixed  $input_list List.
 * @param string $field      Field name.
 * @return array<int|string, mixed>
 */
function wp_list_pluck( $input_list, $field ): array {
	if ( ! is_array( $input_list ) ) {
		return array();
	}

	$plucked = array();

	foreach ( $input_list as $key => $item ) {
		$plucked[ $key ] = is_object( $item ) ? $item->$field : $item[ $field ];
	}

	return $plucked;
}

/*
 * Admin menu, Settings API, and assets.
 */

/**
 * Record an options page and return its hook suffix.
 *
 * @param string   $page_title Page title.
 * @param string   $menu_title Menu title.
 * @param string   $capability Capability.
 * @param string   $menu_slug  Menu slug.
 * @param callable $callback   Render callback.
 * @return string|false
 */
function add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback = '' ) {
	$GLOBALS['oam_test']['options_pages'][] = compact( 'page_title', 'menu_title', 'capability', 'menu_slug', 'callback' );

	return 'settings_page_' . $menu_slug;
}

/**
 * Record a registered setting.
 *
 * @param string               $group Option group.
 * @param string               $name  Option name.
 * @param array<string, mixed> $args  Arguments.
 */
function register_setting( $group, $name, $args = array() ): void {
	$GLOBALS['oam_test']['registered_settings'][] = compact( 'group', 'name', 'args' );
}

/**
 * Record a settings section.
 *
 * @param string   $id       Section ID.
 * @param string   $title    Title.
 * @param callable $callback Callback.
 * @param string   $page     Page slug.
 */
function add_settings_section( $id, $title, $callback, $page ): void {
	$GLOBALS['oam_test']['settings_sections'][] = compact( 'id', 'title', 'callback', 'page' );
}

/**
 * Record a settings field.
 *
 * @param string               $id       Field ID.
 * @param string               $title    Title.
 * @param callable             $callback Callback.
 * @param string               $page     Page slug.
 * @param string               $section  Section ID.
 * @param array<string, mixed> $args     Arguments.
 */
function add_settings_field( $id, $title, $callback, $page, $section = 'default', $args = array() ): void {
	$GLOBALS['oam_test']['settings_fields'][] = compact( 'id', 'title', 'callback', 'page', 'section', 'args' );
}

/**
 * Print the hidden fields of a settings form. WordPress also prints a nonce.
 *
 * @param string $group Option group.
 */
function settings_fields( $group ): void {
	echo '<input type="hidden" name="option_page" value="' . esc_attr( $group ) . '" />';
	echo '<input type="hidden" name="action" value="update" />';
}

/**
 * Print the recorded sections and fields of a page, in the structure that
 * WordPress prints: a heading and the section callback, then one table row per
 * field with the title as the label of the field named by label_for. The
 * titles are printed as given, without escaping, as in WordPress.
 *
 * @param string $page Page slug.
 */
function do_settings_sections( $page ): void {
	foreach ( $GLOBALS['oam_test']['settings_sections'] as $section ) {
		if ( $section['page'] !== $page ) {
			continue;
		}

		echo '<h2>' . $section['title'] . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$section['callback']( $section );
		echo '<table class="form-table" role="presentation">';

		foreach ( $GLOBALS['oam_test']['settings_fields'] as $field ) {
			if ( $field['page'] !== $page || $field['section'] !== $section['id'] ) {
				continue;
			}

			echo '<tr><th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ?? '' ) . '">' . $field['title'] . '</label></th><td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$field['callback']( $field['args'] );
			echo '</td></tr>';
		}

		echo '</table>';
	}
}

/**
 * Print a submit button.
 */
function submit_button(): void {
	echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" /></p>';
}

/**
 * Title of the current admin page, as stored: WordPress does not escape it.
 */
function get_admin_page_title(): string {
	return (string) $GLOBALS['oam_test']['page_title'];
}

/**
 * The checked attribute, as WordPress prints it.
 *
 * @param mixed $checked Value to test.
 * @param mixed $current Value that means checked.
 * @param bool  $display Whether to print it.
 */
function checked( $checked, $current = true, $display = true ): string {
	$result = (string) $checked === (string) $current ? " checked='checked'" : '';

	if ( $display ) {
		echo $result; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	return $result;
}

/**
 * Record an enqueued stylesheet.
 *
 * @param string            $handle  Handle.
 * @param string            $src     URL.
 * @param array<int,string> $deps    Dependencies.
 * @param string|bool|null  $version Version.
 */
function wp_enqueue_style( $handle, $src = '', $deps = array(), $version = false ): void {
	$GLOBALS['oam_test']['styles'][ $handle ] = compact( 'src', 'deps', 'version' );
}

/**
 * URL of a file inside a plugin. The plugin file is recorded, not used: the
 * name of the checkout directory must not reach a test.
 *
 * @param string $path   Path below the plugin directory.
 * @param string $plugin Plugin file.
 */
function plugins_url( $path = '', $plugin = '' ): string {
	$GLOBALS['oam_test']['plugins_url_calls'][] = compact( 'path', 'plugin' );

	return 'https://example.org/wp-content/plugins/optimizations-ace-mc/' . ltrim( $path, '/' );
}

oam_test_reset_state();

require_once dirname( __DIR__ ) . '/optimizations-ace-mc.php';

// What loading the plugin file registered, kept apart from the per-test state.
$GLOBALS['oam_test_load'] = array(
	'hooks'           => $GLOBALS['oam_test']['hooks'],
	'uninstall_hooks' => $GLOBALS['oam_test']['uninstall_hooks'],
);
