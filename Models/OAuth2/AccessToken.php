<?php

namespace Models\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

use \Models\Traits\GitWorkflow;

class AccessToken extends \Models\Abstraction\GitDAO implements AccessTokenEntityInterface
{

	use AccessTokenTrait;
	use EntityTrait;
	use TokenEntityTrait;

	use GitWorkflow;

	protected $repo;

	protected $database = 'oauth/access_token';

	public function __construct(
		\Models\Interfaces\FileSystemInterface $filesystem,
		\Models\Interfaces\GitInterface $git,
		\Models\Interfaces\BagInterface $bag
	){
		parent::__construct($filesystem, $git, $bag);

		// this is necessary to acomplish with specific 
		// models what is being done on the generic
		if( isset($this->git) )
			$this->git->setRepo( $this->config['database-address'] . '/' . $this->database );
	}

}