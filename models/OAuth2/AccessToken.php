<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities;

use League\OAuth2\Server\Entities\Traits;

class AccessToken implements AccessTokenEntityInterface
{

	use AccessTokenTrait;
	use EntityTrait;
	use TokenEntityTrait;

	/**
	 * 
	 */
	public function __construct(){}

	/**
     * @return ClientEntityInterface
     */
    public function getClient(){

    }

    /**
     * @return \DateTime
     */
    public function getExpiryDateTime(){

    }

    /**
     * @return string|int
     */
    public function getUserIdentifier(){

    }

    /**
     * @return ScopeEntityInterface[]
     */
    public function getScopes(){

    }

}