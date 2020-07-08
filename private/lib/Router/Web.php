<?php

use App\Controller\Authentication;
use App\Controller\Welcome;

/**  @var FastRoute\RouteCollector $route */

// Welcome
$route->addRoute('GET', BASE_URL . '/', 'Welcome@index');
$route->addRoute('GET', Welcome::HOME_URL, 'Welcome@dashboard');

// Authentication
$route->addRoute(['GET', 'POST'], Authentication::LOGIN_URL, 'Authentication@login');
$route->addRoute(['GET', 'POST'], Authentication::REGISTER_URL, 'Authentication@register');
$route->addRoute('GET', Authentication::LOGOUT_URL, 'Authentication@logout');