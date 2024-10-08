<?php

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__ . '/src');

$config = new PhpCsFixer\Config();
return $config->setRules([
	'full_opening_tag' => false,
	'@PSR12' => true,
	'@Symfony' => true,
	'array_syntax' => ['syntax' => 'short'],
	'yoda_style' => false,
	'class_attributes_separation' => [
		'elements' => ['method' => 'one', 'property' => 'one', 'trait_import' => 'one']
	],
	'trailing_comma_in_multiline' => ['elements' => ['arguments', 'arrays', 'match', 'parameters']],
])
	->setFinder($finder);
