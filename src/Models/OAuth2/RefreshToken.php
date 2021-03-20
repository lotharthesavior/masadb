<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class RefreshToken implements RefreshTokenEntityInterface
{

    use RefreshTokenTrait;
    use EntityTrait;

    /**
     *
     */
    public function __construct()
    {

    }

}
