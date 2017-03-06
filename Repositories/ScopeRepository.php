<?php

namespace Repositories;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

use League\OAuth2\Server\Entities\ClientEntityInterface;

// scopes
use \Models\OAuth2\Scopes\Administrator;
use \Models\OAuth2\Scopes\Basic;

class ScopeRepository implements ScopeRepositoryInterface
{

	/**
	 * 
	 */
	public function __construct(){

	}

	/**
     * Return information about a scope.
     *
     * @param string $identifier The scope identifier
     *
     * @return ScopeEntityInterface
     */
    public function getScopeEntityByIdentifier($identifier){

        $class = '\\Models\\OAuth2\\Scopes\\' . ucfirst($identifier);

        $scope_model = new $class;

        return $scope_model;

    }

    /**
     * Given a client, grant type and optional user identifier validate the set of scopes requested are valid and optionally
     * append additional scopes or remove requested scopes.
     *
     * @param ScopeEntityInterface[] $scopes
     * @param string                 $grantType
     * @param ClientEntityInterface  $clientEntity
     * @param null|string            $userIdentifier
     *
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ){

        // $scope_model = new Scope;

        return $scopes;

    }

}