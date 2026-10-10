/**
 * Helpers for the info window template tests.
 *
 * The plugin has no script of its own. What it sends to the browser is a piece
 * of an Underscore template, which WP Store Locator compiles and runs for each
 * info window on the map. The template and the store data come from the
 * plugin's own PHP (render-info-window.php); the template is compiled here
 * with the Underscore version WordPress ships, the way WP Store Locator does
 * it, and the result is read with jsdom.
 */
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import { JSDOM } from 'jsdom';
import _ from 'underscore';

const renderScript = fileURLToPath( new URL( './render-info-window.php', import.meta.url ) );

/** Templates and store data rendered by PHP. */
export const rendered = JSON.parse(
	execFileSync( 'php', [ '-d', 'display_errors=stderr', renderScript ], { encoding: 'utf8' } )
);

/** The template settings WP Store Locator sets (setUnderscoreSettings() in its wpsl-setup.js). */
export const storeLocatorSettings = {
	evaluate: /<%(.+?)%>/g,
	interpolate: /<%=(.+?)%>/g,
	escape: /<%-(.+?)%>/g,
};

/** The values WP Store Locator 3.1 gives a template when the store data lacks them (wpsl-helpers.js). */
const optionalPlaceholders = {
	description: '',
	location_status: '',
	hours_status: '',
	hours: '',
	thumb: '',
	categories: '',
	permalink: '',
	email: '',
	url: '',
	phone: '',
	fax: '',
	distance: '',
	distance_unit: '',
};

/** Stand-ins for the functions WP Store Locator adds to the store data. */
const templateHelpers = {
	createInfoWindowActions: ( id ) => `<div class="wpsl-info-actions" data-for="${ id }"></div>`,
	formatPhoneNumber: ( number ) => number,
};

/**
 * Compile a template.
 *
 * @param {string} template - Underscore template.
 * @param {Object} settings - Template settings; none means the Underscore defaults.
 * @returns {Function} The compiled template.
 */
export function compile( template, settings = storeLocatorSettings ) {
	return _.template( template, settings );
}

/**
 * Build an info window the way WP Store Locator does in getInfoWindowTemplate().
 *
 * @param {string} template - Underscore template.
 * @param {Object} store - Store data.
 * @returns {Object} The markup, its info window element, and the paragraphs and action links in it.
 */
export function infoWindow( template, store ) {
	const data = _.defaults( _.extend( {}, store, templateHelpers ), optionalPlaceholders );
	const html = compile( template )( data );
	const element = JSDOM.fragment( html ).querySelector( '.wpsl-info-window' );

	return {
		html,
		element,
		paragraphs: Array.from( element.querySelectorAll( ':scope > p' ) ),
		actions: element.querySelector( ':scope > .wpsl-info-actions' ),
	};
}

/**
 * The part of the template that the plugin added.
 *
 * @param {string} original - Template before the plugin's filter.
 * @param {string} filtered - Template after it.
 * @returns {string} The added text; empty when the filter did more than add one piece.
 */
export function addedPart( original, filtered ) {
	let start = 0;

	while ( start < original.length && original[ start ] === filtered[ start ] ) {
		start++;
	}

	// The piece can begin with the characters that follow it, so step back to the start of its line.
	while ( start > 0 && filtered[ start - 1 ] !== '\n' ) {
		start--;
	}

	const added = filtered.slice( start, start + filtered.length - original.length );

	return filtered.slice( 0, start ) + filtered.slice( start + added.length ) === original ? added : '';
}
