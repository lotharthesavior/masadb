<?php

if ($config['settings']['env'] === APP_ENV_PROD) {

    // Init our repositories
    $accessTokenRepository = new \Repositories\AccessTokenRepository($container); // instance of AccessTokenRepositoryInterface

    // Path to authorization server's public key
    $publicKey = $config['settings']['public_key'];

    // Setup the authorization server
    $server = new \League\OAuth2\Server\ResourceServer(
        $accessTokenRepository,
        $publicKey
    );

}
