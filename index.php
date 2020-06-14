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
use Pachico\SlimSwoole\BridgeManager;
use Slim\Http;

// Load configuration
$config_json = file_get_contents("config.json");
$config['settings'] = json_decode($config_json, true);

date_default_timezone_set($config['settings']['timezone']);

$app = new \Slim\App($config);

$container = $app->getContainer();

// base
include "app/oauth2.php";
include "app/middlewares.php";
include "app/controllers.php";
include "routes.php";

$bridgeManager = new BridgeManager($app);

/**
 * We start the Swoole server
 */
$http = new swoole_http_server("0.0.0.0", 80);

/**
 * We register the on "start" event
 */
$http->on("start", function (\swoole_http_server $server) {
    echo sprintf('Swoole http server is started at http://%s:%s', $server->host, $server->port), PHP_EOL;
});

/**
 * We register the on "request event, which will use the BridgeManager to transform request, process it
 * as a Slim request and merge back the response
 *
 */
$http->on(
    "request",
    function (swoole_http_request $swooleRequest, swoole_http_response $swooleResponse) use ($bridgeManager) {
        $bridgeManager->process($swooleRequest, $swooleResponse)->end();
    }
);

$http->start();