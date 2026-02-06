<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;

return RectorConfig::configure()
	->withPreparedSets(
		symfonyCodeQuality: true,
	)
	->withRules([
		ReturnTypeFromStrictNativeCallRector::class,
		ClassPropertyAssignToConstructorPromotionRector::class,
	])
	->withComposerBased(symfony: true);
