<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities\ScopeEntityInterface;

// use League\OAuth2\Server\Entities\Traits;

class Scope implements ScopeEntityInterface
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

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        
    }

}