<?php declare(strict_types=1);

$rules = [
    '@PSR12' => true,
    'declare_strict_types' => true,
    'linebreak_after_opening_tag' => false,
    'blank_line_after_opening_tag' => false,
    'no_unused_imports' => true,
    'concat_space' => ['spacing' => 'one'], // makes "$a.$b" look like "$a . $b"
    'phpdoc_align' => ['align' => 'left'],
    'class_attributes_separation' => [
        // places a single space between properties and methods
        'elements' => ['method' => 'one', 'property' => 'only_if_meta', 'trait_import' => 'one']
    ],
    'blank_line_before_statement' => true,

// php 8.1 relevant fixers
//    \PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer::name() => true,
//    \PhpCsFixerCustomFixers\Fixer\ConstructorEmptyBracesFixer::name() => true,
//    \PhpCsFixerCustomFixers\Fixer\NoDoctrineMigrationsGeneratedCommentFixer::name() => true,
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
