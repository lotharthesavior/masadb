<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities\ClientEntityInterface;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ClientTrait;

use \Models\GitModel;

/**
 * Format of data:
 * {
 *     "name": string,
 *     "user_id": integer,
 *     "redirect_uri": string
 * }
 */
class Clients extends GitModel implements ClientEntityInterface
{

	use EntityTrait;
	use ClientTrait;

	use \Models\Traits\GitWorkflow;

	protected $database = "oauth/clients";

	protected $repo;

	/**
	 * 
	 */
	public function find( $id ){

		$client_loaded = parent::find( $id );

		$client_loaded->setIdentifier( $id );

		return $this;

	}

}