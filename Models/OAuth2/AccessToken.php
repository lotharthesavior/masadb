<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

use \Models\Traits\GitWorkflow;

class AccessToken extends \Models\GitModel implements AccessTokenEntityInterface
{

	use AccessTokenTrait;
	use EntityTrait;
	use TokenEntityTrait;

	use GitWorkflow;

	protected $repo;

	protected $database = 'oauth/access_token';

}