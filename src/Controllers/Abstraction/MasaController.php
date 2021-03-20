<?php

namespace Controllers\Abstraction;

use \Repositories\AccessTokenRepository;

abstract class MasaController
{

    /**
     *
     */
    protected function oauthBefore()
    {

        // Init our repositories
        $accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface

        // Path to authorization server's public key
        $publicKey = $this->container->get('settings')['public_key'];

        // Setup the authorization server
        $server = new \League\OAuth2\Server\ResourceServer(
            $accessTokenRepository,
            $publicKey
        );

        new \League\OAuth2\Server\Middleware\ResourceServerMiddleware($server);

    }

}
