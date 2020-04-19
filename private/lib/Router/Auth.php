<?php

use App\Controller\Auth;

/**  @var FastRoute\RouteCollector $route */
$route->addRoute(['GET', 'POST'], Auth::LOGIN_URL, 'Auth@login');
$route->addRoute(['GET', 'POST'], Auth::REGISTER_URL, 'Auth@register');
$route->addRoute('GET', Auth::LOGOUT_URL, 'Auth@logout');