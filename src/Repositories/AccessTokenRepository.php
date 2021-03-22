<?php

namespace Repositories;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

use Models\OAuth2\AccessToken;
use Models\Interfaces\FileSystemInterface;
use Models\Interfaces\GitInterface;
use Models\FileSystem\FileSystemBasic;
use Models\Git\GitBasic;
use Models\Interfaces\BagInterface;
use Models\Bag\BagBasic;
use Repositories\Abstraction\AbstractRepository;

class AccessTokenRepository extends AbstractRepository implements AccessTokenRepositoryInterface
{
    /**
     * Create a new access token
     *
     * @param ClientEntityInterface $clientEntity
     * @param array $scopes ScopeEntityInterface[]
     * @param mixed $userIdentifier
     *
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        $userIdentifier = null
    ): AccessTokenEntityInterface
    {
        /** @var AccessToken $access_token_model */
        $access_token_model = $this->container->get(AccessToken::class);

        return $access_token_model;
    }

    /**
     * Persists a new access token to permanent storage.
     *
     * @todo persist the elements present
     *
     * @param AccessTokenEntityInterface $accessTokenEntity
     *
     * @return void
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        /** @var AccessToken $access_token_model */
        $access_token_model = $this->container->get(AccessToken::class);

        $access_token_model->save([
            'id' => null,
            'content' => [
                'address' => null,
                'content' => json_encode([

                    // string this is randomly generated unique identifier (of 80+ characters in length) for the access token.
                    'identifier' => $accessTokenEntity->getIdentifier(),

                    // \DateTime the expiry date and time of the access token.
                    'expiry_date' => $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),

                    // string|null the user identifier represented by the access token.
                    'user_identifier' => $accessTokenEntity->getUserIdentifier(),

                    // ScopeEntityInterface[] an array of scope entities
                    'scopes' => $accessTokenEntity->getScopes(),

                    // string the identifier of the client who requested the access token
                    'client_identifier' => $accessTokenEntity->getClient()->getIdentifier()

                ]),
            ],
        ]);
    }

    /**
     * Revoke an access token.
     *
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId)
    {
        /** @var AccessToken $access_token_model */
        $access_token = $this->container->get(AccessToken::class);

        $result = $access_token->search('identifier', $tokenId);
    }

    /**
     * Check if the access token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isAccessTokenRevoked($tokenId)
    {

    }

}
