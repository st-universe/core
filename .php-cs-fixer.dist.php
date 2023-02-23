<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        'src',
        'tests'
    ])
    ->exclude([
        'Public/assets',
        'OrmProxy',
    ])
;
$config = new PhpCsFixer\Config();
$config->setRules(
    [
        '@PSR12' => true,
        '@PHP81Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false
        ],
        'no_unused_imports' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
	        'imports_order' => ['const', 'class', 'function'],
        ],
    ]
)->setFinder($finder);

return $config;
