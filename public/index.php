<?php

use App\Controller\Authentication;
use Jenssegers\Blade\Blade;

require_once(dirname(__DIR__) . '/private/init.php');

//$blade = new Blade(TEMPLATE_PATH, TEMPLATE_CACHE_PATH);

//$parameters = [
//    'assets_url' => ASSETS_URL,
//    'post_url' => BASE_URL . '/authenticate/login/',
//];

//echo $blade->render('dashboard', $parameters);




// sources
// https://primer.style/css/utilities/box-shadow#default

$dispatcher = FastRoute\simpleDispatcher(static function(FastRoute\RouteCollector $r) {
    $auth = new Authentication();

    $r->addRoute(['GET', 'POST'], Authentication::AUTH_LOGIN_URL, 'Authentication@login');
    $r->addRoute('GET', '/', 'Templates@login');
    $r->addRoute('GET', '/test', 'test.php');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_GET['url'] ?? '';
$uri ="/{$uri}";

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        var_dump($uri);
        echo '404';
        //print_r($routeInfo);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        var_dump($routeInfo);
        var_dump($_POST); exit();
        array_shift($routeInfo);
        [$handler, $vars] = $routeInfo;

        if (strpos($handler,'@') === false) {
            throw new \RuntimeException('Controller and method name was not provided');
        }
        [$className, $method] = explode('@', $handler);

        // Path to all controllers, concatenates with class name
        $fullClassPath = "\\App\\Controller\\{$className}";
        if (!class_exists($fullClassPath)) {
            throw new \RuntimeException("Class ${fullClassPath} does not exist");
        }

        // Creates controller instance
        $controller = new $fullClassPath();
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method {$method} was not found in class {$fullClassPath}");
        }

        // Executes the method
        $controller->{$method}($vars);
        break;
}