<?php

namespace Controllers\Abstraction;

use Repositories\AccessTokenRepository;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use League\OAuth2\Server\ResourceServer;

abstract class MasaController
{

    /**
     * @return void
     */
    protected function oauthBefore(): void
    {
        /**
         * Init repositories.
         *
         * @var AccessTokenRepositoryInterface
         */
        $accessTokenRepository = new AccessTokenRepository();

        // Path to authorization server's public key.
        $publicKey = $this->container->get('settings')['public_key'];

        // Setup the authorization server.
        $server = new ResourceServer(
            $accessTokenRepository,
            $publicKey
        );

        new ResourceServerMiddleware($server);
    }

}
