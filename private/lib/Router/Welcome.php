<?php

use App\Controller\Welcome;

/**  @var FastRoute\RouteCollector $route */
$route->get(Welcome::HOME_URL, 'Welcome@index');