<?php

use App\Database\Repository\CategoryRepository;

require_once(__DIR__ . '/../private/init.php');

$r = CategoryRepository::getByName('diary 1');

print_r($r);
//foreach ($r as $data) {
//    /** @var Category $data */
//    $categoryName = $data->getCategoryName();
//    $data->setCategoryName($categoryName . ' 1');
//    $data->save();
//}

/**
 * Save a new note to the database (untested)
 */
$category = CategoryRepository::getByName('diary 1');
$categoryId = $category->getId();
$now = time();
$newNote = new \App\Database\Model\Note();

$newNote->setCategoryId($categoryId)
        ->setContext('this is a test note')
        ->setCreatedTimestamp($now)
        ->setLastUpdatedTimestamp($now)
        ->save()
        ->flush();

