<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRiskyAllowed(true)
    //->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        // PER-CS3.0 standard
        '@PER-CS' => true,
        '@PER-CS:risky' => true,

        // Additional strict rules for your project
        'strict_param' => true,
        'strict_comparison' => true,
        'declare_strict_types' => true,

        // Array formatting
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arrays',
                'arguments',
                'parameters',
            ],
        ],

        // Import and namespace
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'no_unused_imports' => true,

        // Code style
        'cast_spaces' => ['space' => 'none'],
        'concat_space' => ['spacing' => 'one'],
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '=>' => 'single_space',
                '=' => 'single_space',
            ],
        ],

        // Functions and methods
        'method_chaining_indentation' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'void_return' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false,
            'after_heredoc' => false,
        ],
        'no_spaces_around_offset' => [
            'positions' => [
                'inside',
                'outside'
            ],
        ],
        'single_space_around_construct' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'throw',
                'use',
            ],
        ],

        // Classes
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
            ],
        ],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],

        // Comments and PHPDoc
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_var_without_name' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'allow_unused_params' => false,
            'remove_inheritdoc' => false,
        ],
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_return_self_reference' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'alpha',
        ],
        'fully_qualified_strict_types' => [
            'import_symbols' => true,
        ],

        // Security and best practices
        'no_php4_constructor' => true,
        'no_unreachable_default_argument_value' => true,
        'random_api_migration' => true,

        // Case conversion
        'lowercase_keywords' => true,
        'lowercase_cast' => true,
        'constant_case' => ['case' => 'lower'],
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,
        'native_constant_invocation' => [
            'fix_built_in' => true,
            'include' => [
                'DIRECTORY_SEPARATOR',
                'PHP_SAPI',
                'PHP_VERSION_ID',
            ],
            'scope' => 'namespaced',
        ],

        // Whitespace
        'blank_line_after_opening_tag' => false,
        'linebreak_after_opening_tag' => false,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,

        // Control structures
        'yoda_style' => [
            'always_move_variable' => false,
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
    ])
    ->setFinder(
        Finder::create()
            ->in([
                __DIR__ . '/app',
                __DIR__ . '/bin',
                __DIR__ . '/migrations',
                __DIR__ . '/router',
                __DIR__ . '/tests',
                __DIR__ . '/www',
            ])
            ->name('*.php'),
    );
