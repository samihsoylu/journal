<?php

declare(strict_types=1);

use Psr\EventDispatcher\EventDispatcherInterface;
use SamihSoylu\Journal\Framework\Kernel;
use SamihSoylu\Journal\Framework\Routing\Controller\ControllerResolver;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;

require_once dirname(__DIR__) . '/config/bootstrap.php';

$kernel = Kernel::boot(Request::createFromGlobals());

$request = $kernel->container->get(Request::class);
$dispatcher = $kernel->container->get(EventDispatcherInterface::class);

/**
 * Loads all controllers
 */
$controllersPath = dirname(__DIR__) . '/src/Presentation/Controller/Web';
$loader = new AttributeDirectoryLoader(
    new FileLocator($controllersPath),
    new AttributeRouteControllerLoader()
);
$routes = $loader->load($controllersPath);

/**
 * Setup router
 */
$matcher = new UrlMatcher($routes, new RequestContext());
$dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));

/**
 * Prepares Kernel to instantiate
 */
$controllerResolver = $kernel->container->get(ControllerResolver::class);
$argumentResolver = $kernel->container->get(ArgumentResolver::class);
$httpKernel = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);

/**
 * Invokes controller
 */
try {
    $response = $httpKernel->handle($request);
} catch (NotFoundHttpException $exception) {
    $response = new Response('Not found', Response::HTTP_NOT_FOUND);
}

$response->send();
$httpKernel->terminate($request, $response);

// https://symfony.com/doc/current/create_framework/http_kernel_controller_resolver.html
// look into caching next time