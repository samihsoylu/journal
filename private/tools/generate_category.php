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
    printf("Usage: %s diary\n", basename($argv[0]));
    printf("Usage: %s diary work food exercise\n", basename($argv[0]));
    exit();
}
array_shift($argv);

$repository = new CategoryRepository();
foreach ($argv as $categoryName) {

    // Creates a new category model and assigns a name
    $category = new Category();
    $category->setName($categoryName);

    // Queue for saving in to the database
    $repository->queue($category);

    echo "Queued: {$categoryName}\n";
}

// Save queued database categories
$repository->save();

echo "All categories saved!\n";
