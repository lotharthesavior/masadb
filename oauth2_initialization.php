<?php

// Init our repositories
$clientRepository = new \Repositories\ClientRepository(); // instance of ClientRepositoryInterface
$scopeRepository = new \Repositories\ScopeRepository(); // instance of ScopeRepositoryInterface
$accessTokenRepository = new \Repositories\AccessTokenRepository(); // instance of AccessTokenRepositoryInterface

// Path to public and private keys
$privateKey = '/home/vagrant/Code/gitdev/private.key';
//$privateKey = new CryptKey('file://path/to/private.key', 'passphrase'); // if private key has a pass phrase
$publicKey = '/home/vagrant/Code/gitdev/public.key';

// Setup the authorization server
$server = new \League\OAuth2\Server\AuthorizationServer(
    $clientRepository,
    $accessTokenRepository,
    $scopeRepository,
    $privateKey,
    $publicKey
);

// Enable the client credentials grant on the server
$server->enableGrantType(
    new \League\OAuth2\Server\Grant\ClientCredentialsGrant(),
    new \DateInterval('PT1H') // access tokens will expire after 1 hour
);