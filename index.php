<?php

/**
 * @todo 1. create class for navigation through the repository
 * @todo 2. create navigation itself though the repository
 */

if( !file_exists("config.json") ){
	header("Location: install.php");
}

require __DIR__ . '/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Load configuration
$config_json = file_get_contents("config.json");
$config['settings'] = json_decode($config_json, true);

$app = new \Slim\App($config);

$container = $app->getContainer();


// OAuth2 initialization ---------------------------------------------------------------

// Init our repositories
$accessTokenRepository = new \Repositories\AccessTokenRepository(); // instance of AccessTokenRepositoryInterface

// Path to authorization server's public key
$publicKey = $config['settings']['public_key'];
      
// Setup the authorization server
$server = new \League\OAuth2\Server\ResourceServer(
    $accessTokenRepository,
    $publicKey
);

// OAuth2 initialization ---------------------------------------------------------------


// Middlewares -------------------------------------------------------------------------

// $app->add(new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server));

// Middlewares -------------------------------------------------------------------------


// Controllers -------------------------------------------------------------------------

$container['MasaDBController'] = function($c) {
    return new Controllers\MasaDBController($c);
};

$container['TicketsController'] = function($c) {
    return new Controllers\TicketsController($c);
};

$container['UsersController'] = function($c) {
    return new Controllers\UsersController($c);
};

$container['HomeController'] = function($c) {
    return new Controllers\HomeController($c);
};

$container['ClientsController'] = function($c) {
    return new Controllers\ClientsController($c);
};

$container['RepositoriesController'] = function($c) {
    return new Controllers\RepositoriesController($c);
};

$container['OAuthController'] = function($c) {
    return new Controllers\OAuthController($c);
};

// -------------------------------------------------------------------------------------


// Routes ------------------------------------------------------------------------------

include "routes.php";

// -------------------------------------------------------------------------------------


// TODO: erase if not used until 2017/03/10
// $origin = "*";
// header("Access-Control-Allow-Origin: " . $origin);

$app->run();
