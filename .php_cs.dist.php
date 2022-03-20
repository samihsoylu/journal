<?php declare(strict_types=1);

$rules = [
    //'declare_strict_types' => true,
    'linebreak_after_opening_tag' => false,
    'blank_line_after_opening_tag' => false,
    'no_unused_imports' => true,
];

$config = new PhpCsFixer\Config();
return $config
    ->setRules($rules)
    //->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->in(__DIR__)
    );
