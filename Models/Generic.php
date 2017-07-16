<?php

namespace Models;

use \Git\Coyl\Git;

class Generic extends GitModel
{

    use Traits\GitWorkflow;

    // add this to make the GitModel knows where to find the record
    use Traits\BagUtilities;

	protected $repo;

	protected $database = '';

	/**
	 * 
	 */
	public function setDatabase( $database ){
		$this->database = $database;
	}

	/**
	 * 
	 */
	public function setClientId( $client_id ){
		$this->client_id = $client_id;
	}

	/**
	 * 
	 */
	public function setRepo( $repo ){
		$this->repo = \Coyl\Git\Git::open($repo);
	}

}
