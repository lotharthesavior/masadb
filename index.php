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

// ------------------------------------
// Global Constants
// ------------------------------------

if (!defined('APP_ENV_DEVELOP')) {
    define('APP_ENV_DEVELOP', 'develop');
}

if (!defined('APP_ENV_STAGING')) {
    define('APP_ENV_STAGING', 'staging');
}

if (!defined('APP_ENV_PROD')) {
    define('APP_ENV_PROD', 'production');
}

// ------------------------------------
// Load configuration
// ------------------------------------

function config(): array {
    $config_json = file_get_contents('config.json');
    $config['settings'] = json_decode($config_json, true);

    // get environment config overrides
    $env_config = __DIR__ . '/config.json-' . $config['settings']['env'];
    if (file_exists($env_config)) {
        $env_config_contents = file_get_contents($env_config);
        $config_json_env = json_decode($env_config_contents, true);
        $config['settings'] = array_merge($config['settings'], $config_json_env);
    }

    return $config;
}
$config = config();

date_default_timezone_set($config['settings']['timezone']);

// ------------------------------------
// Start Application
// ------------------------------------

$app = new \Slim\App($config);

$container = $app->getContainer();

// base
if ($config['settings']['env'] === APP_ENV_PROD) {
    include "app/oauth2.php";
}
include "app/middlewares.php";
include "app/controllers.php";
include "routes.php";

// ------------------------------------
// Start Swoole Server
// ------------------------------------

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