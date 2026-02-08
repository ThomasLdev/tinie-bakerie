<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->exclude([
        'var',
        'vendor',
        'node_modules',
    ])
    ->name('*.php')
    ->notName('*.tpl.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        // PSR Standards
        '@PSR1' => true,
        '@PSR2' => true,
        '@PSR12' => true,

        // Symfony Standards
        '@Symfony' => true,
        '@Symfony:risky' => true,

        // Modern PHP Standards
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PHP81Migration' => true,
        '@PHP82Migration' => true,
        '@PHP83Migration' => true,
        '@PHP84Migration' => true,

        // Array notation
        'array_syntax' => ['syntax' => 'short'],
        'array_indentation' => true,
        'trim_array_spaces' => true,
        'normalize_index_brace' => true,

        // Blank lines
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try', 'if', 'for', 'foreach', 'while', 'do', 'switch', 'declare'],
        ],
        'blank_line_after_namespace' => true,
        'blank_lines_before_namespace' => true,

        // Casing
        'constant_case' => ['case' => 'lower'],
        'lowercase_keywords' => true,
        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,
        'native_function_type_declaration_casing' => true,

        // Class notation
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'class_definition' => [
            'multi_line_extends_each_single_line' => true,
            'single_item_single_line' => true,
            'single_line' => true,
        ],
        'final_internal_class' => true,
        'no_null_property_initialization' => true,
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
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
        'protected_to_private' => true,
        'self_static_accessor' => true,
        'single_class_element_per_statement' => true,
        'visibility_required' => true,

        // Comments
        'comment_to_phpdoc' => true,
        'header_comment' => false,
        'multiline_comment_opening_closing' => true,
        'no_empty_comment' => true,
        'single_line_comment_style' => true,

        // Control structures
        'elseif' => true,
        'include' => true,
        'no_alternative_syntax' => true,
        'no_superfluous_elseif' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unneeded_curly_braces' => true,
        'no_useless_else' => true,
        'switch_continue_to_break' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
        'yoda_style' => false,

        // Declare statements
        'declare_equal_normalize' => true,
        'declare_strict_types' => true,

        // Function notation
        'function_declaration' => true,
        'function_typehint_space' => true,
        'lambda_not_used_import' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false,
        ],
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
            'scope' => 'namespaced',
            'strict' => true,
        ],
        'no_spaces_after_function_name' => true,
        'no_unreachable_default_argument_value' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'return_type_declaration' => true,
        'static_lambda' => true,
        'void_return' => true,

        // Imports
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => false,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'no_leading_import_slash' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,

        // Language constructs
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'declare_parentheses' => true,
        'dir_constant' => true,
        'error_suppression' => true,
        'is_null' => true,
        'modernize_strpos' => true,
        'modernize_types_casting' => true,
        'no_alias_functions' => true,
        'no_alias_language_construct_call' => true,
        'no_mixed_echo_print' => true,
        'pow_to_exponentiation' => true,
        'random_api_migration' => true,
        'set_type_to_cast' => true,

        // Operators
        'assign_null_coalescing_to_coalesce_equal' => true,
        'binary_operator_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'increment_style' => ['style' => 'pre'],
        'logical_operators' => true,
        'new_with_braces' => true,
        'no_space_around_double_colon' => true,
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => ['only_booleans' => true],
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_elvis_operator' => true,
        'ternary_to_null_coalescing' => true,
        'unary_operator_spaces' => true,

        // PHPDoc
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_line_span' => [
            'const' => 'single',
            'method' => 'multi',
            'property' => 'single',
        ],
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order' => true,
        'phpdoc_order_by_value' => true,
        'phpdoc_return_self_reference' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_tag_type' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,

        // Return notation
        'no_useless_return' => true,
        'return_assignment' => true,
        'simplified_null_return' => true,

        // Semicolons
        'multiline_whitespace_before_semicolons' => true,
        'no_empty_statement' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'semicolon_after_instruction' => true,
        'space_after_semicolon' => true,

        // Strict
        'strict_comparison' => true,
        'strict_param' => true,

        // String notation
        'escape_implicit_backslashes' => true,
        'explicit_string_variable' => true,
        'heredoc_to_nowdoc' => true,
        'no_binary_string' => true,
        'simple_to_complex_string_variable' => true,
        'single_quote' => true,
        'string_line_ending' => true,

        // Whitespace
        'compact_nullable_typehint' => true,
        'heredoc_indentation' => true,
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'case',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'throw',
                'use',
            ],
        ],
        'no_spaces_around_offset' => true,
        'no_whitespace_in_blank_line' => true,
        'types_spaces' => true,

        // Symfony specific
        'fopen_flag_order' => true,
        'fopen_flags' => true,
        'get_class_to_class_keyword' => true,
        'implode_call' => true,
        'no_php4_constructor' => true,
        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_dedicate_assert_internal_type' => true,
        'php_unit_expectation' => true,
        'php_unit_fqcn_annotation' => true,
        'php_unit_internal_class' => true,
        'php_unit_method_casing' => true,
        'php_unit_mock' => true,
        'php_unit_mock_short_will_return' => true,
        'php_unit_namespaced' => true,
        'php_unit_no_expectation_annotation' => true,
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_strict' => true,
        'php_unit_test_annotation' => true,
        'php_unit_test_case_static_method_calls' => true,
        'php_unit_test_class_requires_covers' => false, // Set to true if you want @covers annotations
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');
