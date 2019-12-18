#!/usr/bin/env php
<?php

use App\Database\Model\Category;

/**
 * Use this file to add a new category to the database from the command line. In the -c parameter you can provide more
 * than one category name separated by a comma.
 *
 * Usage: php SaveNewCategory.php -c 'diary,food,work'
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

foreach ($categoryNames as $categoryName) {
    $categoryName = ucfirst(strtolower($categoryName));

    $db = (new Category())->setCategoryName($categoryName)
        ->setCreatedTimestamp(time())
        ->setLastUpdatedTimestamp(time())
        ->save();

    echo "Registered: {$categoryName}\n";
}

/**
 * @var \Doctrine\ORM\EntityManager $db
 */
$db->flush();

echo 'Successfully saved new categories.';