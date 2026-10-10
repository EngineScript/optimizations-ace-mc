/**
 * Tests for what the plugin adds to WP Store Locator's info window template.
 *
 * Run with `npm test`. See helpers.mjs for how the template is built and run.
 */
import assert from 'node:assert/strict';
import { test } from 'node:test';
import { addedPart, compile, infoWindow, rendered } from './helpers.mjs';

const { original, template, stores, markup } = rendered;
const added = addedPart( original, template );

/**
 * Markup with each run of white space as one space. A template block that prints nothing still leaves its line break.
 *
 * @param {string} html - Markup.
 * @returns {string} The markup to compare.
 */
function collapsed( html ) {
	return html.replace( /\s+/g, ' ' );
}

test( 'the plugin adds one piece to the template and changes nothing else', () => {
	assert.notEqual( added, '', 'The filtered template is the original with one piece added.' );
	assert.ok( template.indexOf( added ) < template.indexOf( '<%= createInfoWindowActions(' ), 'The piece comes before the action links.' );
} );

test( 'every value the added piece reads is in the store data PHP builds', () => {
	const printed = Array.from( added.matchAll( /<%[=-]\s*([A-Za-z_$][\w$]*)\s*%>/g ), ( match ) => match[ 1 ] );
	const tested = Array.from( added.matchAll( /typeof\s+([A-Za-z_$][\w$]*)/g ), ( match ) => match[ 1 ] );

	assert.deepEqual( printed, [ 'terms' ], 'The piece prints the category text and nothing else.' );
	assert.equal(
		( added.match( /<%[=-]/g ) || [] ).length,
		printed.length,
		'Every value the piece prints is a plain name that this test can read.'
	);
	assert.deepEqual( tested, printed, 'Each printed value is tested for existence first.' );

	for ( const [ name, store ] of Object.entries( stores ) ) {
		for ( const key of printed ) {
			assert.equal( typeof store[ key ], 'string', `The data of the store ${ name } has ${ key } as text.` );
		}
	}
} );

test( 'the template compiles with WP Store Locator\'s settings and with the Underscore defaults', () => {
	assert.equal( typeof compile( template ), 'function' );
	assert.equal( typeof compile( template, {} ), 'function' );
} );

test( 'categories are shown in front of the action links', () => {
	const view = infoWindow( template, stores.with_categories );
	const categories = view.paragraphs.at( -1 );

	assert.equal( categories.textContent, 'Certifications: Gold Dealer, Service & Repair' );
	assert.equal( categories.nextElementSibling, view.actions, 'The action links follow the categories.' );
	assert.equal( categories.children.length, 0, 'The paragraph holds text only.' );
} );

test( 'everything else WP Store Locator shows stays as it was', () => {
	const view = infoWindow( template, stores.with_categories );
	const before = infoWindow( original, stores.with_categories );

	assert.equal( view.paragraphs.length, before.paragraphs.length + 1 );

	view.paragraphs.at( -1 ).remove();

	assert.equal( collapsed( view.element.innerHTML ), collapsed( before.element.innerHTML ) );
} );

test( 'a store without categories gets no extra paragraph', () => {
	assert.equal( stores.without_categories.terms, '' );
	assert.equal(
		collapsed( infoWindow( template, stores.without_categories ).html ),
		collapsed( infoWindow( original, stores.without_categories ).html )
	);
} );

test( 'store data cached before the setting was enabled does not stop the info window', () => {
	const cached = rendered.cached_store;

	assert.equal( 'terms' in cached, false, 'The cached data has no category text.' );
	assert.equal( collapsed( infoWindow( template, cached ).html ), collapsed( infoWindow( original, cached ).html ) );

	// The same data stops a template that reads the value without testing that it exists.
	const unguarded = template.replace( 'typeof terms !== "undefined" && terms', 'terms' );

	assert.notEqual( unguarded, template );
	assert.throws( () => infoWindow( unguarded, cached ), ReferenceError );
} );

test( 'markup in a category name is shown as text', () => {
	const view = infoWindow( template, stores.with_markup );
	const categories = view.paragraphs.at( -1 );

	assert.equal( view.element.querySelectorAll( 'script, img' ).length, 0 );
	assert.equal( categories.children.length, 0 );
	assert.ok( markup.names.every( ( name ) => name.startsWith( '<' ) ), 'The names hold markup.' );
	assert.equal( categories.textContent, 'Certifications: ' + markup.names.join( ', ' ) );
} );

test( 'markup in the category label is shown as text', () => {
	const view = infoWindow( rendered.template_with_markup_label, stores.with_categories );
	const categories = view.paragraphs.at( -1 );

	assert.equal( view.element.querySelectorAll( 'script, b' ).length, 0 );
	assert.equal( categories.children.length, 0 );
	assert.ok( markup.label.includes( '<' ), 'The label holds markup.' );
	assert.equal( categories.textContent, markup.label + ' Gold Dealer, Service & Repair' );
} );

test( 'a category named 0 is shown', () => {
	assert.equal( infoWindow( template, stores.named_zero ).paragraphs.at( -1 ).textContent, 'Certifications: 0' );
} );
