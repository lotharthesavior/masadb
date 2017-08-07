<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities\ClientEntityInterface;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ClientTrait;

use \Models\GitDAO;

/**
 * Format of data:
 * {
 *     "name": string,
 *     "user_id": integer,
 *     "redirect_uri": string
 * }
 */
class Clients extends \Models\Abstraction\GitDAO implements ClientEntityInterface
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

		$result_parsed = json_decode( $client_loaded, true );

		$this->file_content = $this->filesystem->loadFileObject( $result_parsed );

		$this->setIdentifier( $id );

		return $this;

	}

}