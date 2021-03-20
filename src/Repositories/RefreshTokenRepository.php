<?php

namespace Repositories;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

use \Models\OAuth2\RefreshToken;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * Creates a new refresh token
     *
     * @return RefreshTokenEntityInterface
     */
    public function getNewRefreshToken()
    {

        $refresh_token_model = new RefreshToken;

        return $refresh_token_model;

    }

    /**
     * Create a new refresh token_name.
     *
     * @param RefreshTokenEntityInterface $refreshTokenEntity
     * @todo persist the present data
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {

        // string this is randomly generated unique identifier (of 80+ characters in length) for the refresh token.
        // $refreshTokenEntity->getIdentifier();

        // \DateTime the expiry date and time of the access token.
        // $refreshTokenEntity->getExpiryDateTime();

        // string the linked access token’s identifier.
        // $refreshTokenEntity->getAccessToken()->getIdentifier();

    }

    /**
     * Revoke the refresh token.
     *
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId)
    {

    }

    /**
     * Check if the refresh token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isRefreshTokenRevoked($tokenId)
    {

    }

}
