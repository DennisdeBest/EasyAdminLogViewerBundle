<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;

return static function (RectorConfig $rectorConfig): void {
	$rectorConfig->sets([
		SymfonySetList::SYMFONY_71,
		SetList::PHP_82,
		SymfonySetList::SYMFONY_CODE_QUALITY,
		SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
		DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
		SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
	]);

	$rectorConfig->rules([
		ReturnTypeFromStrictNativeCallRector::class,
		ClassPropertyAssignToConstructorPromotionRector::class,
	]);
};
