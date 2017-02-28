<?php

/**
 * @todo 1. create class for navigation through the repository
 * @todo 2. create navigation itself though the repository
 */

// use Psr\Http\Message\ResponseInterface;
// use Psr\Http\Message\ServerRequestInterface;

require __DIR__ . '/vendor/autoload.php';

$templates = new League\Plates\Engine('themes/masa1');

$container = new League\Container\Container;

$container->share('response', Zend\Diactoros\Response::class);
$container->share('request', function () {
    return Zend\Diactoros\ServerRequestFactory::fromGlobals(
        $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
    );
});

$container->share('emitter', Zend\Diactoros\Response\SapiEmitter::class);

include "oauth2_initialization.php";

include "route.php";

$response = $route->dispatch($container->get('request'), $container->get('response'));

$origin = "*";
header("Access-Control-Allow-Origin: " . $origin);

$container->get('emitter')->emit($response);