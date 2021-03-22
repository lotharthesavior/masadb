<?php

use Models\Bag\BagBasic;
use Models\FileSystem\FileSystemBasic;
use Models\Generic;
use Models\Git\GitBasic;
use Models\OAuth2\AccessToken;
use Models\OAuth2\AuthCode;
use Models\OAuth2\Clients;
use Models\OAuth2\RefreshToken;
use Models\OAuth2\User;

$container[Generic::class] = function ($c) {
    return new Generic(
        new FileSystemBasic, // FileSystemInterface
        new GitBasic,        // GitInterface
        new BagBasic         // BagInterface
    );
};

$container[Clients::class] = function ($c) {
    return new Clients(
        new FileSystemBasic, // FileSystemInterface
        new GitBasic,        // GitInterface
        new BagBasic         // BagInterface
    );
};

$container[AccessToken::class] = function ($c) {
    return new AccessToken(
        new FileSystemBasic, // FileSystemInterface
        new GitBasic,        // GitInterface
        new BagBasic         // BagInterface
    );
};

$container[RefreshToken::class] = function ($c) {
    return new RefreshToken(
        new FileSystemBasic, // FileSystemInterface
        new GitBasic,        // GitInterface
        new BagBasic         // BagInterface
    );
};

$container[AuthCode::class] = function ($c) {
    return new AuthCode(
        new FileSystemBasic, // FileSystemInterface
        new GitBasic,        // GitInterface
        new BagBasic         // BagInterface
    );
};

$container[User::class] = function ($c) {
    return new User(
        new FileSystemBasic, // FileSystemInterface
        new GitBasic,        // GitInterface
        new BagBasic         // BagInterface
    );
};
