<?php

use Repositories\AccessTokenRepository;
use Repositories\AuthCodeRepository;
use Repositories\ClientRepository;
use Repositories\RefreshTokenRepository;
use Repositories\ScopeRepository;
use Repositories\UserRepository;

$container[AccessTokenRepository::class] = function ($c) {
    return new AccessTokenRepository($c);
};

$container[AuthCodeRepository::class] = function ($c) {
    return new AuthCodeRepository($c);
};

$container[ClientRepository::class] = function ($c) {
    return new ClientRepository($c);
};

$container[RefreshTokenRepository::class] = function ($c) {
    return new RefreshTokenRepository($c);
};

$container[ScopeRepository::class] = function ($c) {
    return new ScopeRepository($c);
};

$container[UserRepository::class] = function ($c) {
    return new UserRepository($c);
};
