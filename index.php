<?php

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Psy\Configuration;
use Slim\App;
use Psy\Shell;

global $app, $config;

include_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'constants.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'helpers.php';

(
    require __DIR__
        . DIRECTORY_SEPARATOR. 'src'
        . DIRECTORY_SEPARATOR . 'system_checks'
        . DIRECTORY_SEPARATOR . 'config.php'
)();

$config = config();

(
    require __DIR__
        . DIRECTORY_SEPARATOR . 'src'
        . DIRECTORY_SEPARATOR . 'system_checks'
        . DIRECTORY_SEPARATOR . 'settings.php'
)();

$app = new App($config);

$container = $app->getContainer();

include_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'oauth2.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'controllers.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'models.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'repositories.php';
include_once __DIR__ . DIRECTORY_SEPARATOR . "routes.php";

date_default_timezone_set($config['settings']['timezone']);

$is_shell = in_array('--shell', $argv);

if (!$is_shell && $config['settings']['swoole']) {
    start_server($config);
} else if ($is_shell) {
    $shell = new Shell();
    $shell->setScopeVariables(get_defined_vars());
    $shell->run();
}

