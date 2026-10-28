<?php
/**
 * Rector configuration.
 *
 * Rector keeps the plugin's PHP on current idioms. CI runs it as a dry run
 * (`composer rector`), which fails when any rule would change the code.
 *
 * @package OptimizationsAceMc
 */

use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\If_\ExplicitNullableObjectInstanceRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\Instanceof_\Rector\Ternary\FlipNegatedTernaryInstanceofRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;

return RectorConfig::configure()
	->withPaths(
		[
			__DIR__ . '/optimizations-ace-mc.php',
			__DIR__ . '/includes',
		]
	)
	->withBootstrapFiles(
		[
			__DIR__ . '/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php',
			__DIR__ . '/stubs/woocommerce.php',
		]
	)
	->withPhpSets( php83: true )
	->withPreparedSets(
		deadCode: true,
		codeQuality: true,
		typeDeclarations: true,
		earlyReturn: true,
	)
	->withSkip(
		[
			// Turns hook callbacks into closures, which has_action() and remove_action() cannot find.
			ArrayToFirstClassCallableRector::class,
			// The plugin header must be the first docblock of the main file.
			SafeDeclareStrictTypesRector::class,
			// WordPress-Docs requires @var on properties.
			RemoveUselessVarTagRector::class,
			// Writes `$x === []`, which is not a Yoda condition.
			SimplifyEmptyCheckOnEmptyArrayRector::class,
			// A plain null check reads better than a negated instanceof.
			ExplicitNullableObjectInstanceRector::class,
			FlipNegatedTernaryInstanceofRector::class,
			// The tests reset the coordinator singleton between cases by writing its settings.
			ReadOnlyPropertyRector::class => [ __DIR__ . '/includes/class-optimizations-ace-mc.php' ],
			ReadOnlyClassRector::class    => [ __DIR__ . '/includes/class-optimizations-ace-mc.php' ],
		]
	);
