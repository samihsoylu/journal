<?php

use Jenssegers\Blade\Blade;

require_once(dirname(__DIR__) . '/private/init.php');

$blade = new Blade(BASE_PATH . '/private/templates/',BASE_PATH . '/private/cache/blade/');

$parameters = [
    'assets_url' => ASSETS_URL,
    'post_url' => BASE_URL . '/login/submit',
];

echo $blade->render('login', $parameters);

// sources
// https://primer.style/css/utilities/box-shadow#default