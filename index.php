<?php

require __DIR__ . '/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http;

global $app, $config;

if(!file_exists("config.json")){
    echo <<<OUTPUT
\nFile not created: config.json\n
This is part of the installation step that you can find here:\n
    https://repository.wordstree.com/masa.tech/masadb/src/master/Readme.md";\n\n
OUTPUT;
    return;
}

include_once __DIR__ . "/src/constants.php";
include_once __DIR__ .  "/src/helpers.php";

$config = config();

if ($config['settings']['env'] === APP_ENV_PROD) {
    include_once __DIR__ . "/src/oauth2.php";
}

$app = new \Slim\App($config);

$container = $app->getContainer();

include_once __DIR__ . "/src/middlewares.php";
include_once __DIR__ . "/src/controllers.php";
include_once __DIR__ .  "/routes.php";

date_default_timezone_set($config['settings']['timezone']);

if ($config['settings']['swoole']) {
    start_server();
}