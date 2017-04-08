<?php

namespace Models\Traits;

use \Lotharthesavior\BagItPHP\BagIt;

trait BagUtilities
{

	/**
	  * @param Integer $id
	  */
	protected function createBagForRecord( $id ){

	 	$bag = new BagIt( $this->config['database-address'] . '/' . $this->database . '/' . $id );

	 	$bag->addFile( $this->config['database-address'] . '/' . $this->database . '/' . $id . '.json', $id . '.json' );

		$bag->update();

		unlink( $this->config['database-address'] . '/' . $this->database . '/' . $id . '.json' );

	}

}