<?php

namespace Models\OAuth2\Scopes;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities\ScopeEntityInterface;

// use League\OAuth2\Server\Entities\Traits;

class Basic implements ScopeEntityInterface
{

    protected $identifier = 'basic';

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * Get the scope's identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {

        return $this->identifier;

    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->identifier;
    }

}
