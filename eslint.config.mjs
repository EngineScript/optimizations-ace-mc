/**
 * ESLint configuration: the WordPress JavaScript coding standards for
 * the tests of the store locator info window template.
 *
 * The "recommended-with-formatting" set checks formatting with ESLint's own
 * rules. The plain "recommended" set hands formatting to Prettier, whose
 * output does not match the WordPress style without the WordPress fork.
 */
import wordpress from '@wordpress/eslint-plugin';
import globals from 'globals';

export default [
	{
		ignores: [ '.private/**', 'coverage/**', 'node_modules/**', 'vendor/**' ],
	},
	...wordpress.configs[ 'recommended-with-formatting' ],
	{
		languageOptions: {
			globals: { ...globals.browser },
		},
		linterOptions: {
			reportUnusedDisableDirectives: 'error',
			reportUnusedInlineConfigs: 'error',
		},
	},
	{
		files: [ 'tests/js/**' ],
		languageOptions: {
			globals: { ...globals.node },
		},
	},
];
