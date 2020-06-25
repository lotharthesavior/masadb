<?php

require __DIR__ . '/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http;

global $app, $config;

include_once __DIR__ . "/src/constants.php";
include_once __DIR__ .  "/src/helpers.php";

(require __DIR__ . '/src/system_checks/config.php')();

$config = config();

(require __DIR__ . '/src/system_checks/settings.php')();

include_once __DIR__ . "/src/oauth2.php";

$app = new \Slim\App($config);

$container = $app->getContainer();

include_once __DIR__ . "/src/middlewares.php";
include_once __DIR__ . "/src/controllers.php";
include_once __DIR__ . "/routes.php";

date_default_timezone_set($config['settings']['timezone']);

if ($config['settings']['swoole']) {
    start_server();
}