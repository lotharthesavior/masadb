<?php

namespace Repositories;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Models\OAuth2\RefreshToken;
use Repositories\Abstraction\AbstractRepository;

class RefreshTokenRepository extends AbstractRepository implements RefreshTokenRepositoryInterface
{
    /**
     * Creates a new refresh token
     *
     * @return RefreshTokenEntityInterface
     */
    public function getNewRefreshToken(): RefreshTokenEntityInterface
    {
        /** @var RefreshToken $access_token_model */
        $refresh_token_model = $this->container->get(RefreshToken::class);

        return $refresh_token_model;
    }

    /**
     * Create a new refresh token_name.
     *
     * @todo persist the present data
     *
     * @param RefreshTokenEntityInterface $refreshTokenEntity
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        /** @var RefreshToken $access_token_model */
        $refresh_token_model = $this->container->get(RefreshToken::class);

        $refresh_token_model->save([
            'id' => null,
            'content' => [
                'address' => null,
                'content' => json_encode([

                    // string this is randomly generated unique identifier (of 80+ characters in length) for the access token.
                    'identifier' => $refreshTokenEntity->getIdentifier(),

                    // \DateTime the expiry date and time of the access token.
                    'expiry_date' => $refreshTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),

                    // string|null the access token.
                    'access_token' => $refreshTokenEntity->getAccessToken(),

                ]),
            ],
        ]);
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
