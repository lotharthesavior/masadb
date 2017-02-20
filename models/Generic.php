<?php

namespace Models;

use \Git\Coyl\Git;

class Generic extends Model
{
	protected $repo;

	protected $database = '';

	public function __construct(){}

	/**
	 * 
	 */
	public function setDatabase( $database ){
		$this->database = $database;
	}

	/**
	 * 
	 */
	public function setRepo( $repo ){
		$this->repo = \Coyl\Git\Git::open($repo);
	}

}