<?php

namespace Models;

use \Git\Coyl\Git;

class Repositories extends Model
{
	protected $repo;

	protected $database = 'repositories';

	public function __construct(){

		$this->repo = \Coyl\Git\Git::open('data');

	}

}