<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities;

use League\OAuth2\Server\Entities\Traits;

class AuthCode implements AuthCodeEntityInterface
{

	use EntityTrait;
	use TokenEntityTrait;
	use AuthCodeTrait;

	/**
	 * 
	 */
	public function __construct(){

    }

}