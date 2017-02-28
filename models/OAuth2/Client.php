<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities;

use League\OAuth2\Server\Entities\Traits;

class Client implements ClientEntityInterface
{

	use EntityTrait;
	use ClientTrait;

	/**
	 * 
	 */
	public function __construct(){

	}

}