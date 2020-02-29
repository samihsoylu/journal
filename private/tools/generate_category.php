#!/usr/bin/env php
<?php

use App\Database\Model\Category;
use App\Database\Repository\CategoryRepository;

/**
 * Use this file to add a new category to the database from the command line. In the -c parameter you can provide more
 * than one category name by using a comma.
 *
 * Usage: generate_category.php -c 'diary,food,work'
 */

require(__DIR__ . '/../init.php');

# Get argument
$options = getopt('c:');

# Check if data is provided
if (!isset($options['c'])) {
    printf("Usage: %s -c 'diary,food,work'\n", basename($_SERVER['PHP_SELF']));
    exit();
}

# Separate multiple categories by a coma
$categoryNames = explode(',', $options['c']);

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

echo "All categories saved!";
