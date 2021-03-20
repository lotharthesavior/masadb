<?php

$container['HomeController'] = function ($c) {
    return new Controllers\HomeController($c);
};

$container['MasaDBController'] = function ($c) {
    return new Controllers\MasaDBController($c);
};

$container['OAuthController'] = function ($c) {
    return new Controllers\OAuthController($c);
};
