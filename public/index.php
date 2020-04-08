<?php

use Jenssegers\Blade\Blade;

require_once(dirname(__DIR__) . '/private/init.php');

$blade = new Blade(TEMPLATE_PATH, TEMPLATE_CACHE_PATH);

$parameters = [
    'assets_url' => ASSETS_URL,
    'post_url' => BASE_URL . '/authenticate/login/',
];

echo $blade->render('dashboard', $parameters);

// sources
// https://primer.style/css/utilities/box-shadow#default