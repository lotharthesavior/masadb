<?php

namespace Models;

use \Git\Coyl\Git;

use League\Flysystem\Filesystem;

use League\Flysystem\Adapter\Local;

class Tickets extends GitModel
{

	use Traits\GitWorkflow;

	// add this to make the GitModel knows where to find the record
	use Traits\BagUtilities;

	protected $repo;

	protected $database = 'tickets';

	/**
	 * @param Array $client_data | eg.: ["id" => {int}, "content" => {array}]
	 */
	public function save( Array $client_data ){

		$content = parent::save( $client_data );

		return $content;
		
	}

}
