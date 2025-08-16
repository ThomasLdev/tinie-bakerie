<?php

$finder = new PhpCsFixer\Finder()
    ->in(__DIR__)
    ->exclude('var')
;

return new PhpCsFixer\Config()
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        // Enforce explicit imports for classes, functions, and constants
        'global_namespace_import' => [
            'import_classes' => true,
            'import_functions' => false,
            'import_constants' => false,
        ],
        // Do not remove unused imports automatically
        'no_unused_imports' => false,
        // Prefer imports over FQCNs
        'fully_qualified_strict_types' => false,
    ])
    ->setFinder($finder)
;
