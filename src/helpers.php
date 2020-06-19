<?php

use Pachico\SlimSwoole\BridgeManager;

/**
 * Start Application Configurations
 */
function config() : array  {
	global $config;

	$config_json = file_get_contents(__DIR__ . '/../config.json');
    $config['settings'] = json_decode($config_json, true);

    // get environment config overrides
    $env_config = __DIR__ . '/../config.json-' . $config['settings']['env'];
    if (file_exists($env_config)) {
        $env_config_contents = file_get_contents($env_config);
        $config_json_env = json_decode($env_config_contents, true);
        $config['settings'] = array_merge($config['settings'], $config_json_env);
    }

    return $config;
}

/**
 * Start Application Swoole Server
 */
function start_server() : void {
	global $app;

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
}