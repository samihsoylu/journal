<?php declare(strict_types=1);

$rules = [
    '@PhpCsFixer' => true,
    '@PhpCsFixer:risky' => true,
    '@PSR12' => true,
    '@PSR12:risky' => true,
    '@PHP8x4Migration' => true,
    '@PHP8x4Migration:risky' => true,
    'declare_strict_types' => true,
    'no_unused_imports' => true,
    'concat_space' => ['spacing' => 'one'], // makes "$a.$b" look like "$a . $b"
    'phpdoc_align' => ['align' => 'left'],
    'class_attributes_separation' => [
        // places a single space between properties and methods
        'elements' => ['method' => 'one', 'property' => 'none', 'trait_import' => 'one', 'const' => 'none']
    ],
    'blank_line_before_statement' => [
        'statements' => ['return', 'throw', 'try', 'if', 'for', 'foreach', 'while', 'do', 'switch']
    ],

    // Disable Yoda style - use normal comparison order ($var === null instead of null === $var)
    'yoda_style' => [
        'equal' => false,
        'identical' => false,
        'less_and_greater' => false,
    ],

    // PHP 8.0+ features
    'modernize_types_casting' => true,
    'no_unset_cast' => true,

    // PHP 8.1+ features
    'octal_notation' => true,

    // PHP 8.2+ features
    'nullable_type_declaration' => true,

    // Type declarations
    'fully_qualified_strict_types' => true,
    'native_function_type_declaration_casing' => true,

    // Import attributes
    'attribute_empty_parentheses' => true,
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => true,
        'import_functions' => false,
    ],

    // Function invocation - remove \ prefix for built-in functions in namespaced code
    'native_function_invocation' => [
        'include' => [],
        'scope' => 'namespaced',
        'strict' => true,
    ],

    // Make all classes final (except abstract and Doctrine entities)
    'final_class' => true,

    // Spacing rules
    'not_operator_with_space' => true,
    'binary_operator_spaces' => [
        'default' => 'single_space',
    ],
    'type_declaration_spaces' => [
        'elements' => ['function', 'property'],
    ],
    'return_type_declaration' => ['space_before' => 'one'],

    // Format empty method/function bodies on a single line
    'single_line_empty_body' => true,

    // Force constructor promoted properties on separate lines when there are multiple parameters
    'multiline_promoted_properties' => [
        'minimum_number_of_parameters' => 2,
        'keep_blank_lines' => false,
    ],

    // Ensure all multiline function/method arguments are fully multiline (one per line)
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline',
    ],

    // Add trailing commas to multiline parameters, arrays, arguments, and match expressions
    'trailing_comma_in_multiline' => [
        'elements' => ['arguments', 'arrays', 'match', 'parameters'],
        'after_heredoc' => false,
    ],

    // PHPDoc formatting - ensure consistency
    'phpdoc_separation' => [
        'groups' => [
            ['deprecated', 'link', 'see', 'since'],
            ['author', 'copyright', 'license'],
            ['category', 'package', 'subpackage'],
            ['property', 'property-read', 'property-write'],
            ['param', 'return'],
        ],
    ],
    'phpdoc_summary' => true, // Ensure PHPDoc summary ends with period
    'phpdoc_trim_consecutive_blank_line_separation' => true,

    // Strict comparisons and type safety
    'strict_comparison' => true, // Force === and !== instead of == and !=
    'strict_param' => true, // Enforce strict types in function parameters

    // Array formatting
    'array_syntax' => ['syntax' => 'short'], // Force [] instead of array()
    'no_whitespace_before_comma_in_array' => true,
    'whitespace_after_comma_in_array' => ['ensure_single_space' => true],

    // Modern PHP features
    'modernize_strpos' => true, // Use str_contains/str_starts_with/str_ends_with (PHP 8.0+)
    'get_class_to_class_keyword' => true, // Replace get_class($obj) with $obj::class (PHP 8.0+)

    // Import ordering
    'ordered_imports' => [
        'imports_order' => ['class', 'function', 'const'],
        'sort_algorithm' => 'alpha',
    ],

    // Visibility and final
    'visibility_required' => ['elements' => ['property', 'method', 'const']], // Always declare visibility
    'final_internal_class' => true, // Make @internal classes final
];

$config = new PhpCsFixer\Config();
return $config
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude(['vendor', 'private/cache/'])
        ->in(__DIR__)
    );
