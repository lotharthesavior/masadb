<?php

namespace Models;

use \Git\Coyl\Git;

class Notes extends Model
{
	protected $repo;

	protected $database = 'notes';

	public function __construct(){

		$this->repo = \Coyl\Git\Git::open('data');

	}

}