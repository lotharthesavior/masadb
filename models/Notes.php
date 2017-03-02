<?php

namespace Models;

use \Git\Coyl\Git;

use League\Flysystem\Filesystem;

use League\Flysystem\Adapter\Local;

class Notes extends GitModel
{

	use Traits\GitWorkflow;

	protected $repo;

	protected $database = 'notes';

}