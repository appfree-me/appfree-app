<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['var', 'node_modules'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'yoda_style' => false,
        'single_line_throw' => false,
        'declare_strict_types' => true,
    ])
    ->setFinder($finder)
;
