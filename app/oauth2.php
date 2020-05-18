<?php

// Init our repositories
$accessTokenRepository = new \Repositories\AccessTokenRepository(); // instance of AccessTokenRepositoryInterface

// clearstatcache(); // this might be needed to avoid issues with certificates permissions when configuring the db

// Path to authorization server's public key
$publicKey = $config['settings']['public_key'];
      
// Setup the authorization server
$server = new \League\OAuth2\Server\ResourceServer(
    $accessTokenRepository,
    $publicKey
);