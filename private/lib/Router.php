<?php

declare(strict_types=1);

namespace App;

use App\Controller\Error;
use App\Exception\UserException;
use App\Utility\ExceptionHandler;
use DI\Container;
use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use RuntimeException;
use Throwable;

use function FastRoute\simpleDispatcher;

/**
 * Class Router loads all pre-defined routes, and instantiates a simple dispatcher.
 */
final class Router
{
    private const string PATH_TO_ROUTES = BASE_PATH . '/private/lib/Router';

    public static function route(Container $container) : void
    {
        $dispatcher = simpleDispatcher(static function (RouteCollector $route) : void {
            // Load all pre-defined routes
            $routeFiles = glob(self::PATH_TO_ROUTES . '/*.php');

            foreach ($routeFiles as $routeFile) {
                require_once $routeFile;
            }
        });

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = rtrim((string) $_SERVER['REQUEST_URI'], '/');

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        $errorController = $container->get(Error::class);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found, route does not exist
                http_response_code(404);
                $errorController->renderNotFoundPage();

                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                // $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                http_response_code(405);
                $errorController->methodNotAllowed();

                break;
            case Dispatcher::FOUND:
                array_shift($routeInfo);

                [$handler, $routeParameters] = $routeInfo;

                if ( ! str_contains((string) $handler, '@')) {
                    throw new RuntimeException('Controller and method name was not provided');
                }

                [$className, $methodName] = explode('@', (string) $handler);

                // Path to all controllers, concatenates with class name
                $fullClassPath = '\App\Controller\\' . $className;

                if ( ! class_exists($fullClassPath)) {
                    throw new RuntimeException(sprintf('Controller class %s does not exist', $className));
                }

                // Creates controller instance from DI container, and ensures provided methodName exists
                $controller = $container->get($fullClassPath);
                $controller->setRouteParameters($routeParameters);

                if ( ! method_exists($controller, $methodName)) {
                    throw new RuntimeException(sprintf('Method %s was not found in class %s', $methodName, $className));
                }

                try {
                    $controller->{$methodName}();
                } catch (Exception|Throwable $exception) {
                    self::handleException($exception, $controller, $methodName);
                }

                break;
        } // end of switch
    }

    private static function handleException(Throwable $exception, object $controller, string $methodName) : void
    {
        $handleException = new ExceptionHandler($exception);

        if ($exception instanceof UserException) {
            $handleException->userException($controller, $methodName);

            return;
        }

        $handleException->genericException();
    }
}
