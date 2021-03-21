<?php

use Pachico\SlimSwoole\BridgeManager;
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server\Port;

/**
 * Start Application Configurations
 *
 * @return array
 */
function config(): array
{
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
 *
 * @param array $config
 *
 * @return void
 */
function start_server(array $config): void
{
    global $app;

    $bridgeManager = new BridgeManager($app);

    $server = new Server("0.0.0.0", 80);

    if ($config['settings']['protocol'] === 'https') {

        /** @var Port $ssl_port */
        $ssl_port = $server->listen("0.0.0.0", 443, SWOOLE_SOCK_TCP | SWOOLE_SSL);

        $http_server_config = [
            'ssl_cert_file' => $config['settings']['cert'],
            'ssl_key_file' => $config['settings']['private_key'],
        ];

        if (isset($config['settings']['ssl_verify_depth'])) {
            $http_server_config['ssl_verify_depth'] = 10;
        }

        if (isset($config['settings']['ssl_verify_peer'])) {
            $http_server_config['ssl_verify_peer'] = $config['settings']['ssl_verify_peer'];
        }

        if (isset($config['settings']['open_http2_protocol'])) {
            $http_server_config['open_http2_protocol'] = $config['settings']['open_http2_protocol'];
        }

        if (isset($config['settings']['ssl_allow_self_signed'])) {
            $http_server_config['ssl_allow_self_signed'] = $config['settings']['ssl_allow_self_signed'];
        }

        $http_server_config['open_http_protocol'] = true;
        $ssl_port->set($http_server_config);

    }

    $server->on("start", function (Server $server) {
        echo sprintf('Swoole http server is started at http://%s:%s', $server->host, $server->port), PHP_EOL;
    });

    $server->on("request", function (Request $swooleRequest, Response $swooleResponse) use ($bridgeManager) {
        $bridgeManager->process($swooleRequest, $swooleResponse)->end();
    });

    $server->start();
}
