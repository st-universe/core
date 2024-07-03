<?php

/**
 * Execute on command line via 'vendor/bin/php-cs-fixer fix'
 */
$finder = PhpCsFixer\Finder::create()
    ->in([
        'src/admin', 
        'src/Component', 
        'src/Config', 
        'src/Exception', 
        'src/Lib', 
        'src/Module', 
        'src/Orm', 
        'tests'
        ])
    ->exclude(['Public/assets']);
$config = new PhpCsFixer\Config();
$config->setRules(
    [
        '@DoctrineAnnotation' => true,
        '@PSR12' => true,
        '@PHP84Migration' => true,
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
        'trailing_comma_in_multiline' => ['elements' => []]
    ]
)->setFinder($finder);

return $config;
