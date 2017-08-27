<?php

/**
 * @todo 1. create class for navigation through the repository
 * @todo 2. create navigation itself though the repository
 */

date_default_timezone_set("America/Vancouver");

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

include "app/oauth2.php";

include "app/middlewares.php";

include "app/controllers.php";

include "routes.php";

$app->run();
