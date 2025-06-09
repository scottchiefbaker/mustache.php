<?php

use PhpCsFixer\Config;

$config = new Config();

$config->setRules([
    '@PSR12' => true,
    'no_unused_imports' => true,
    'single_quote' => true,
    'trailing_comma_in_multiline' => true,
    'array_syntax' => ['syntax' => 'short'],
]);

$finder = $config->getFinder()
    ->in('bin')
    ->in('src')
    ->in('test');

return $config;
