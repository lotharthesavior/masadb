<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities;

use League\OAuth2\Server\Entities\Traits;

class RefreshToken implements RefreshTokenEntityInterface
{

	use RefreshTokenTrait;
	use EntityTrait;

	/**
	 * 
	 */
	public function __construct(){

    }

}