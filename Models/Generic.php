<?php

namespace Models;

use \Git\Coyl\Git;

/**
 * Class for Generic Model.
 * 
 * @author Savio Resende <savio@savioresende.com.br>
 */

class Generic extends \Models\Abstraction\GitDAO implements \Models\Interfaces\GenericInterface
{

    use Traits\GitWorkflow;

    // add this to make the GitModel knows where to find the record
    use Traits\BagUtilities;

	protected $repo;

	protected $database = '';

	/**
	 * Set the database
	 * 
	 * @param String $database
	 * @return void
	 */
	public function setDatabase( $database ){
		$this->database = $database;
	}

	/**
	 * Get the database
	 * 
	 * @return string
	 */
	public function getDatabase(){
		return $this->database;
	}

	/**
	 * Set the Client ID
	 * 
	 * @param String $client_id
	 * @return void
	 */
	public function setClientId( $client_id ){
		$this->client_id = $client_id;
	}

	/**
	 * Get the Client ID
	 * 
	 * @return string
	 */
	public function getClientId(){
		return $this->client_id;
	}

}
