<?php

use App\Controller\Authentication;
use App\Controller\Category;
use App\Controller\Entry;
use App\Controller\Welcome;

/**  @var FastRoute\RouteCollector $route */
// Welcome
$route->addRoute('GET', BASE_URL, 'Welcome@index');
$route->addRoute('GET', Welcome::DASHBOARD_URL, 'Welcome@dashboard');

// Authentication
$route->addRoute('GET', Authentication::LOGIN_URL, 'Authentication@loginView');
$route->addRoute('POST', Authentication::LOGIN_POST_URL, 'Authentication@login');
$route->addRoute('GET', Authentication::REGISTER_URL, 'Authentication@registerView');
$route->addRoute('POST', Authentication::REGISTER_POST_URL, 'Authentication@register');
$route->addRoute('GET', Authentication::LOGOUT_URL, 'Authentication@logout');

// Entries
$route->addRoute('GET', Entry::ENTRIES_URL, 'Entry@index');
$route->addRoute('GET', Entry::CREATE_ENTRY_URL, 'Entry@createView');
$route->addRoute('POST', Entry::CREATE_ENTRY_POST_URL, 'Entry@create');
$route->addRoute('GET', Entry::READ_ENTRY_URL, 'Entry@readView');
$route->addRoute('GET', Entry::UPDATE_ENTRY_URL, 'Entry@updateView');
$route->addRoute('POST', Entry::UPDATE_ENTRY_POST_URL, 'Entry@update');
$route->addRoute('GET', Entry::DELETE_ENTRY_URL, 'Entry@delete');

// Categories
$route->addRoute('GET', Category::CREATE_CATEGORY_URL, 'Category@createView');
$route->addRoute('POST', Category::CREATE_CATEGORY_POST_URL, 'Category@create');
$route->addRoute('GET', Category::UPDATE_CATEGORY_URL, 'Category@updateView');
$route->addRoute('POST', Category::UPDATE_CATEGORY_POST_URL, 'Category@update');
$route->addRoute('GET', Category::CATEGORIES_URL, 'Category@read');
$route->addRoute('GET', Category::DELETE_CATEGORY_URL, 'Category@delete');
