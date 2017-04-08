<?php

namespace Models;

use \Git\Coyl\Git;

class Repositories extends GitModel
{
	protected $repo;

	protected $database = 'repositories';

}