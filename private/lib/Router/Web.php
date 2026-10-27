<?php

declare(strict_types=1);

use App\Controller\Account;
use App\Controller\Authentication;
use App\Controller\Category;
use App\Controller\Entry;
use App\Controller\Media;
use App\Controller\Security;
use App\Controller\Template;
use App\Controller\User;
use App\Controller\Welcome;

/** @var FastRoute\RouteCollector $route */
// Welcome
$route->addRoute('GET', BASE_URL, 'Welcome@index');
$route->addRoute('GET', Welcome::DASHBOARD_URL, 'Welcome@dashboard');

// Authentication
$route->addRoute('GET', Authentication::LOGIN_URL, 'Authentication@loginView');
$route->addRoute(['GET', 'POST'], Authentication::LOGIN_POST_URL, 'Authentication@login');
$route->addRoute('POST', Authentication::LOGOUT_URL, 'Authentication@logout');
$route->addRoute('GET', Authentication::TWO_FACTOR_URL, 'Authentication@twoFactorView');
$route->addRoute('POST', Authentication::TWO_FACTOR_URL, 'Authentication@twoFactor');
$route->addRoute('POST', Authentication::TWO_FACTOR_CANCEL_URL, 'Authentication@cancelTwoFactor');

// Entries
$route->addRoute('GET', Entry::ENTRIES_URL, 'Entry@index');
$route->addRoute('GET', Entry::VIEW_ENTRY_URL, 'Entry@entryView');
$route->addRoute('GET', Entry::CREATE_ENTRY_URL, 'Entry@createView');
$route->addRoute(['GET', 'POST'], Entry::CREATE_ENTRY_POST_URL, 'Entry@create');
$route->addRoute('GET', Entry::UPDATE_ENTRY_URL, 'Entry@updateView');
$route->addRoute(['GET', 'POST'], Entry::UPDATE_ENTRY_POST_URL, 'Entry@update');
$route->addRoute('POST', Entry::DELETE_ENTRY_URL, 'Entry@delete');

// Template
$route->addRoute('GET', Template::TEMPLATES_URL, 'Template@indexView');
$route->addRoute('GET', Template::CREATE_TEMPLATE_URL, 'Template@createView');
$route->addRoute(['GET', 'POST'], Template::CREATE_TEMPLATE_POST_URL, 'Template@create');
$route->addRoute('GET', Template::UPDATE_TEMPLATE_URL, 'Template@updateView');
$route->addRoute(['GET', 'POST'], Template::UPDATE_TEMPLATE_POST_URL, 'Template@update');
$route->addRoute('POST', Template::DELETE_TEMPLATE_URL, 'Template@delete');
$route->addRoute('GET', Template::GET_TEMPLATE_DATA_AS_JSON_URL, 'Template@getTemplateAsJsonView');

// Categories
$route->addRoute('GET', Category::CATEGORIES_URL, 'Category@indexView');
$route->addRoute('GET', Category::CREATE_CATEGORY_URL, 'Category@createView');
$route->addRoute(['GET', 'POST'], Category::CREATE_CATEGORY_POST_URL, 'Category@create');
$route->addRoute('GET', Category::UPDATE_CATEGORY_URL, 'Category@updateView');
$route->addRoute(['GET', 'POST'], Category::UPDATE_CATEGORY_POST_URL, 'Category@update');
$route->addRoute('POST', Category::DELETE_CATEGORY_URL, 'Category@delete');
$route->addRoute('POST', Category::SET_CATEGORY_ORDER_URL, 'Category@setCategoryOrder');

// Users
$route->addRoute('GET', User::USERS_URL, 'User@indexView');
$route->addRoute('GET', User::CREATE_USER_URL, 'User@createView');
$route->addRoute(['GET', 'POST'], User::CREATE_USER_POST_URL, 'User@create');
$route->addRoute('POST', User::DELETE_USER_URL, 'User@delete');
$route->addRoute(['GET', 'POST'], User::UPDATE_USER_URL, 'User@update');
$route->addRoute('GET', User::VIEW_USER_URL, 'User@updateView');

// Account
$route->addRoute('GET', Security::SECURITY_URL, 'Security@indexView');
$route->addRoute('POST', Security::SECURITY_BEGIN_URL, 'Security@begin');
$route->addRoute('POST', Security::SECURITY_CONFIRM_URL, 'Security@confirm');
$route->addRoute('POST', Security::SECURITY_CANCEL_URL, 'Security@cancel');
$route->addRoute('POST', Security::SECURITY_DISABLE_URL, 'Security@disable');
$route->addRoute('POST', Security::SECURITY_REGENERATE_URL, 'Security@regenerate');
$route->addRoute('GET', Account::ACCOUNT_URL, 'Account@indexView');
$route->addRoute('POST', Account::CHANGE_PASSWORD_POST_URL, 'Account@changePassword');
$route->addRoute('POST', Account::UPDATE_EMAIL_POST_URL, 'Account@changeEmail');
$route->addRoute('POST', Account::DELETE_ACCOUNT_POST_URL, 'Account@deleteAccount');
$route->addRoute('POST', Account::UPDATE_WIDGETS_POST_URL, 'Account@updateWidgets');
$route->addRoute('POST', Account::EXPORT_ENTRIES_POST_URL, 'Account@exportEntries');
$route->addRoute('GET', Account::EXPORT_DOWNLOAD_GET_URL, 'Account@downloadEntryExport');
$route->addRoute('POST', Account::EXPORT_DELETE_POST_URL, 'Account@deleteEntryExport');
$route->addRoute('POST', Account::SET_DATE_TIME_ZONE_POST_URL, 'Account@setDateTimeZone');

// Media
$route->addRoute('POST', Media::MEDIA_UPLOAD_POST_URL, 'Media@upload');
$route->addRoute('GET', Media::MEDIA_GET_URL, 'Media@showImage');
