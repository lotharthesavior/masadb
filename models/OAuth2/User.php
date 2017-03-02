<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities\UserEntityInterface;

// use League\OAuth2\Server\Entities\Traits;

class User implements UserEntityInterface
{
	
	/**
	 * 
	 */
	public function __construct(){

    }

    /**
     * Get the scope's identifier.
     *
     * @return string
     */
    public function getIdentifier(){

    }

}