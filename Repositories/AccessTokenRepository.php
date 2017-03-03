<?php

namespace Repositories;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

use League\OAuth2\Server\Entities\ClientEntityInterface;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

use \Models\OAuth2\AccessToken;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{

	/**
	 * 
	 */
	public function __construct(){

	}

	/**
     * Create a new access token
     *
     * @param ClientEntityInterface  $clientEntity
     * @param ScopeEntityInterface[] $scopes
     * @param mixed                  $userIdentifier
     *
     * @return AccessTokenEntityInterface
     */
	public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null){

		$access_token_model = new AccessToken;

		return $access_token_model;

	}

	/**
     * Persists a new access token to permanent storage.
     *
     * @param AccessTokenEntityInterface $accessTokenEntity
     * @todo persist the elements present
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity){

        $access_token_model = new AccessToken;

        $access_token_model->save([
            'id' => null,
            'content' => [

                // string this is randomly generated unique identifier (of 80+ characters in length) for the access token.
                'identifier' => $accessTokenEntity->getIdentifier(),

                // \DateTime the expiry date and time of the access token.
                'expiry_date' => $accessTokenEntity->getExpiryDateTime(),

                // string|null the user identifier represented by the access token.
                'user_identifier' => $accessTokenEntity->getUserIdentifier(),

                // ScopeEntityInterface[] an array of scope entities
                'scopes' => $accessTokenEntity->getScopes(),

                // string the identifier of the client who requested the access token
                'client_identifier' => $accessTokenEntity->getClient()->getIdentifier()

            ]
        ]);

    }

	/**
     * Revoke an access token.
     *
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId){

    }

	/**
     * Check if the access token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isAccessTokenRevoked($tokenId){

    }

}