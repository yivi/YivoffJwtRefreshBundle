<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
                           ->in(__DIR__)
                           ->notPath('DependencyInjection/BundleConfiguration.php');

$config = new PhpCsFixer\Config();
$config->setFinder($finder);

$config
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@PhpCsFixer'             => true,
            '@PHP80Migration'         => true,
            '@PHP80Migration:risky'   => true,
            'strict_param'            => true,
            'binary_operator_spaces'  => [
                'default' => 'align_single_space',
            ],
            'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
            'ordered_imports'         => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
            'phpdoc_to_comment'       => ['ignored_tags' => ['var']],
        ]
    );

return $config;
