<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;

class AuthCode implements AuthCodeEntityInterface
{

    use EntityTrait;
    use TokenEntityTrait;
    use AuthCodeTrait;

    /**
     *
     */
    public function __construct()
    {

    }

}
