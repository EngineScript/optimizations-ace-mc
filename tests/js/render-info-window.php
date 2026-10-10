<?php
/**
 * Render the store locator info window template and store data for the JavaScript tests.
 *
 * Prints one JSON object:
 *
 * - `original` is an info window template shaped like the one WP Store Locator
 *   3.1 builds: a wrapper, the address, a description, a status, and the
 *   action links.
 * - `template` is that template after the plugin's 'wpsl_info_window_template'
 *   filter.
 * - `template_with_markup_label` is the same, with a category label that holds
 *   markup.
 * - `stores` holds the data of four stores after the plugin's 'wpsl_store_meta'
 *   filter.
 * - `cached_store` is store data the filter did not see, as WP Store Locator
 *   keeps it in its cache from before the setting was enabled.
 * - `markup` holds the category names and the label with markup, as they were
 *   given to the plugin. A test compares them with the text the info window
 *   shows.
 *
 * The plugin's hooks are registered the way the plugin registers them, and the
 * filters are applied the way WP Store Locator applies them.
 *
 * WordPress is not loaded; tests/bootstrap.php defines what the plugin calls.
 *
 * @package OptimizationsAceMc
 */

require dirname( __DIR__ ) . '/bootstrap.php';

/**
 * A term as WordPress returns it.
 *
 * @param string $name Term name.
 */
function oam_test_js_term( string $name ): object {
	return (object) array(
		'term_id' => 1,
		'name'    => $name,
	);
}

/**
 * Store data as WP Store Locator passes it to the 'wpsl_store_meta' filter.
 *
 * @param int $id Store ID.
 * @return array<string, mixed>
 */
function oam_test_js_store( int $id ): array {
	return array(
		'id'       => $id,
		'store'    => 'Store ' . $id,
		'address'  => '1 Main Street',
		'address2' => '',
		'city'     => 'Springfield',
		'state'    => 'IL',
		'zip'      => '62701',
		'country'  => 'United States',
		'lat'      => '39.78',
		'lng'      => '-89.65',
	);
}

$oam_test_js_original = '<div data-store-id="<%= id %>" class="wpsl-info-window">' . "\r\n"
	. "\t\t" . '<p>' . "\r\n"
	. "\t\t" . '<strong><%= store %></strong>' . "\r\n"
	. "\t\t" . '<span class="wpsl-street"><%= address %></span>' . "\r\n"
	. "\t\t" . '<% if ( address2 ) { %>' . "\r\n"
	. "\t\t" . '<span class="wpsl-street"><%= address2 %></span>' . "\r\n"
	. "\t\t" . '<% } %>' . "\r\n"
	. "\t\t" . '<span><%= city %> <%= state %> <%= zip %></span>' . "\r\n"
	. "\t\t" . '</p>' . "\r\n"
	. "\t" . '<% if ( typeof description !== "undefined" && description ) { %>' . "\r\n"
	. "\t" . '<p><%= description %></p>' . "\r\n"
	. "\t" . '<% } %>' . "\r\n"
	. "\t" . '<% if ( typeof location_status !== "undefined" && location_status ) { %>' . "\r\n"
	. "\t" . '<p class="wpsl-location-status"><%= location_status %></p>' . "\r\n"
	. "\t" . '<% } %>' . "\r\n"
	. "\t" . '<%= createInfoWindowActions( id, url, typeof permalink !== "undefined" ? permalink : "" ) %>' . "\r\n"
	. '</div>';

$oam_test_js_markup = array(
	'names' => array( '<script>alert(1)</script>', '<img src=x onerror=alert(1)>' ),
	'label' => 'Dealer <b>type</b>:<script>alert(1)</script>',
);

$GLOBALS['oam_test']['terms'] = array(
	11 => array( oam_test_js_term( 'Gold Dealer' ), oam_test_js_term( 'Service & Repair' ) ),
	13 => array_map( 'oam_test_js_term', $oam_test_js_markup['names'] ),
	14 => array( oam_test_js_term( '0' ) ),
);

// A front-end request with the setting on.
$GLOBALS['oam_test']['is_admin'] = false;
$GLOBALS['oam_test']['options'][ Optimizations_Ace_Mc_Settings::OPTION_NAME ] = array( 'wpsl_show_store_categories' => '1' );

( new Optimizations_Ace_Mc_Wpsl_Optimizations( new Optimizations_Ace_Mc_Settings() ) )->register_hooks();

$oam_test_js_template = apply_filters( 'wpsl_info_window_template', $oam_test_js_original );

$oam_test_js_stores = array(
	'with_categories'    => apply_filters( 'wpsl_store_meta', oam_test_js_store( 11 ), 11 ),
	'without_categories' => apply_filters( 'wpsl_store_meta', oam_test_js_store( 12 ), 12 ),
	'with_markup'        => apply_filters( 'wpsl_store_meta', oam_test_js_store( 13 ), 13 ),
	'named_zero'         => apply_filters( 'wpsl_store_meta', oam_test_js_store( 14 ), 14 ),
);

add_filter(
	'optimizations_ace_mc_store_category_label',
	static fn(): string => $oam_test_js_markup['label']
);

$oam_test_js_markup_label = apply_filters( 'wpsl_info_window_template', $oam_test_js_original );

echo json_encode(
	array(
		'original'                   => $oam_test_js_original,
		'template'                   => $oam_test_js_template,
		'template_with_markup_label' => $oam_test_js_markup_label,
		'stores'                     => $oam_test_js_stores,
		'cached_store'               => oam_test_js_store( 15 ),
		'markup'                     => $oam_test_js_markup,
	),
	JSON_THROW_ON_ERROR
);
