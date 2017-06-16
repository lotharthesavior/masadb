<?php

namespace Models;

use \Git\Coyl\Git;

use League\Flysystem\Filesystem;

use League\Flysystem\Adapter\Local;

/**
 * Format data:
 * {
 *     "id": integer,
 *     "name": string,
 *     "email": string
 * }
 */
class Users extends GitModel
{

	use Traits\GitWorkflow;

	protected $repo;

	protected $database = 'users';

}