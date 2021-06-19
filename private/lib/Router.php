<?php declare(strict_types=1);

namespace App;

use App\Controller\Error;
use App\Exception\UserException;
use App\Utility\ExceptionHandler;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

/**
 * Class Router loads all pre-defined routes, and instantiates a simple dispatcher
 */
class Router
{
    protected const PATH_TO_ROUTES = BASE_PATH . '/private/lib/Router';

    public static function route(): void
    {
        $dispatcher = simpleDispatcher(static function (RouteCollector $route) {
            // Load all pre-defined routes
            $routeFiles = glob(self::PATH_TO_ROUTES . '/*.php');
            foreach ($routeFiles as $routeFile) {
                require_once($routeFile);
            }
        });

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = rtrim($_SERVER['REQUEST_URI'], '/');

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        $errorController = new Error([]);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found, route does not exist
                http_response_code(404);
                $errorController->notFound();
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                //$allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                http_response_code(405);
                $errorController->methodNotAllowed();
                break;
            case Dispatcher::FOUND:
                array_shift($routeInfo);

                [$handler, $routeParameters] = $routeInfo;

                if (strpos($handler, '@') === false) {
                    throw new \RuntimeException('Controller and method name was not provided');
                }
                [$className, $methodName] = explode('@', $handler);

                // Path to all controllers, concatenates with class name
                $fullClassPath = "\\App\\Controller\\{$className}";
                if (!class_exists($fullClassPath)) {
                    throw new \RuntimeException("Controller class {$className} does not exist");
                }

                // Creates controller instance, and ensures provided methodName exists
                $controller = new $fullClassPath($routeParameters);
                if (!method_exists($controller, $methodName)) {
                    throw new \RuntimeException("Method {$methodName} was not found in class {$className}");
                }

                try {
                    $controller->{$methodName}();
                } catch (\Throwable|\Exception $exception) {
                    self::handleException($exception, $controller, $methodName);
                }

                break;
        } // end of switch
    }

    private static function handleException(\Throwable $exception, object $controller, string $methodName): void
    {
        $handleException = new ExceptionHandler($exception);

        if ($exception instanceof UserException\NotFoundException) {
            $handleException->renderNotFoundPage();
        }

        if ($exception instanceof UserException) {
            $handleException->userException($controller, $methodName);
        }

        $handleException->genericException();
    }
}
