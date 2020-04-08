#!/usr/bin/env php
<?php

use App\Database\Model\Category;
use App\Database\Repository\CategoryRepository;

/**
 * Use this file to add a new category to the database from the command line.
 *
 * Usage: generate_category.php diary,food,work
 */

require(dirname(__DIR__) . '/init.php');

# Check if data is provided
if (!isset($argv[1])) {
    printf("Usage: %s 'diary,food,work'\n", basename($argv[0]));
    exit();
}

# Separate multiple categories by a coma
$categoryNames = explode(',', $argv[1]);

$repository = new CategoryRepository();
foreach ($categoryNames as $categoryName) {

    // Creates a new category model and assigns a name
    $category = new Category();
    $category->setCategoryName($categoryName);

    // Queue for saving in to the database
    $repository->queue($category);

    echo "Queued: {$categoryName}\n";
}

// Save queued database categories
$repository->save();

echo "All categories saved!\n";
