<?php

declare(strict_types=1);

use PhpCsFixerCustomFixers\Fixer\ConstructorEmptyBracesFixer;
use PhpCsFixerCustomFixers\Fixer\EmptyFunctionBodyFixer;
use PhpCsFixerCustomFixers\Fixer\IssetToArrayKeyExistsFixer;
use PhpCsFixerCustomFixers\Fixer\MultilineCommentOpeningClosingAloneFixer;
use PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer;
use PhpCsFixerCustomFixers\Fixer\NoCommentedOutCodeFixer;
use PhpCsFixerCustomFixers\Fixer\NoDoctrineMigrationsGeneratedCommentFixer;
use PhpCsFixerCustomFixers\Fixer\NoSuperfluousConcatenationFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessCommentFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessParenthesisFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessStrlenFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocArrayStyleFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocNoIncorrectVarAnnotationFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocNoSuperfluousParamFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocTypesTrimFixer;
use PhpCsFixerCustomFixers\Fixer\PromotedConstructorPropertyFixer;
use PhpCsFixerCustomFixers\Fixer\ReadonlyPromotedPropertiesFixer;
use PhpCsFixerCustomFixers\Fixer\SingleSpaceAfterStatementFixer;
use PhpCsFixerCustomFixers\Fixer\SingleSpaceBeforeStatementFixer;
use PhpCsFixerCustomFixers\Fixer\StringableInterfaceFixer;

$rules = [
    '@PSR12' => true,
    '@PSR12:risky' => true,
    '@PHP70Migration' => true,
    '@PHP70Migration:risky' => true,
    '@PHP71Migration' => true,
    '@PHP71Migration:risky' => true,
    '@PHP73Migration' => true,
    '@PHP74Migration' => true,
    '@PHP74Migration:risky' => true,
    '@PHP80Migration' => true,
    '@PHP80Migration:risky' => true,
    '@PHP81Migration' => true,
    '@PHP82Migration' => true,
    'strict_param' => true,
    'array_syntax' => ['syntax' => 'short'],

    'declare_strict_types' => true,
    'no_unused_imports' => true,
    'concat_space' => ['spacing' => 'one'], // makes "$a.$b" look like "$a . $b"
    'phpdoc_align' => ['align' => 'left'],
    'class_attributes_separation' => [
        // places a single space between properties and methods
        'elements' => ['method' => 'one', 'property' => 'only_if_meta', 'trait_import' => 'only_if_meta']
    ],
    'blank_line_before_statement' => true,
    'single_trait_insert_per_statement' => false,

    ConstructorEmptyBracesFixer::name() => true,
    EmptyFunctionBodyFixer::name() => true,
    IssetToArrayKeyExistsFixer::name() => true,
    MultilineCommentOpeningClosingAloneFixer::name() => true,
    MultilinePromotedPropertiesFixer::name() => true,
    NoCommentedOutCodeFixer::name() => true,
    NoDoctrineMigrationsGeneratedCommentFixer::name() => true,
    NoSuperfluousConcatenationFixer::name() => true,
    NoUselessCommentFixer::name() => true,
    NoUselessParenthesisFixer::name() => true,
    NoUselessStrlenFixer::name() => true,
    PhpdocArrayStyleFixer::name() => true,
    PhpdocNoIncorrectVarAnnotationFixer::name() => true,
    PhpdocNoSuperfluousParamFixer::name() => true,
    PhpdocTypesTrimFixer::name() => true,
    PromotedConstructorPropertyFixer::name() => true,
    ReadonlyPromotedPropertiesFixer::name() => true,
    SingleSpaceAfterStatementFixer::name() => true,
    SingleSpaceBeforeStatementFixer::name() => true,
    StringableInterfaceFixer::name() => true,
];

$config = new PhpCsFixer\Config();
return $config
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->registerCustomFixers(new PhpCsFixerCustomFixers\Fixers())
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude(['vendor', 'var'])
        ->in(__DIR__)
    );