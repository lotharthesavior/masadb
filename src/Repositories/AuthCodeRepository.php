<?php

namespace Repositories;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

use \Models\OAuth2\AuthCodeEntityInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * Creates a new AuthCode
     *
     * @return AuthCodeEntityInterface
     */
    public function getNewAuthCode()
    {

        $auth_code_model = new AuthCode;

        return $auth_code_model;

    }

    /**
     * Persists a new auth code to permanent storage.
     *
     * @param AuthCodeEntityInterface $authCodeEntity
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {

        // string this is randomly generated unique identifier (of 80+ characters in length) for the auth code.
        $authCodeEntity->getIdentifier();

        // \DateTime the expiry date and time of the auth code.
        $authCodeEntity->getExpiryDateTime();

        // string|null the user identifier represented by the auth code.
        $authCodeEntity->getUserIdentifier();

        // ScopeEntityInterface[] an array of scope entities
        $authCodeEntity->getScopes();

        // string the identifier of the client who requested the auth code.
        $authCodeEntity->getClient()->getIdentifier();

    }

    /**
     * Revoke an auth code.
     *
     * @param string $codeId
     */
    public function revokeAuthCode($codeId)
    {

    }

    /**
     * Check if the auth code has been revoked.
     *
     * @param string $codeId
     *
     * @return bool Return true if this code has been revoked
     */
    public function isAuthCodeRevoked($codeId)
    {

    }

}
