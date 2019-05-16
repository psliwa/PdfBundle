<?php

$config = PhpCsFixer\Config::create();
$config->setRiskyAllowed(true);
$config->setRules([
    '@PSR2' => true,
    '@Symfony' => true,
    'array_syntax' => [
        'syntax' => 'short',
    ],
    'ordered_imports' => true,
    'php_unit_construct' => true,
    'php_unit_dedicate_assert' => true,
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_trim' => true,
    'psr4' => true,
]);

$finder = PhpCsFixer\Finder::create();
$finder->in([
    'src',
    'tests',
]);

$config->setFinder($finder);

return $config;
