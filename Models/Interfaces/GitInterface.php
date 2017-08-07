<?php

namespace Models\Interfaces;

interface GitInterface
{
	// keep the Git instance for interactions
	// protected $repo;

	/**
	 * Prepare the repository for the job
	 * 
	 * @internal this method is necessary because the instance is 
	 *           created before the address is available. This is
	 *           happens for the possibility of Polymorphism.
	 * @param String $database_address
	 * @return void
	 */
	public function setRepo( $database_address );

	/**
	 * @param String $database  - format expected: "{string}/"
	 */
	public function lsTreeHead( $database = '', \Models\Interfaces\FileSystemInterface $file_system, $is_bag, $database_address );

	/**
	 * Turn the git ls-tree command into Array with
	 * discriminated metadata
	 * 
	 * @internal the $cli_result param "row" is expected to be like this: 
	 *               structure1: "100644 blob 0672e3d1ca4498ea4f6de663764e28f712468b03	oauth/access_token/1.json"
	 * @param String $cli_result
	 * @param Bool $is_db - here is decided if the parsing will fill id 
	 *                      attribute or not
	 * @param \Models\Interfaces\FileSystemInterface $file_system
	 * @return \Ds\Deque
	 */
	public function parseLsTree( $cli_result, $is_db = false, \Models\Interfaces\FileSystemInterface $file_system, $is_bag, $database_address );

	/**
	 * Wrapper for git show command
	 * 
	 * @param String $file
	 * @param String $branch
	 * @return String - command line result
	 */
	public function showFile( $file, $branch = "master" );

}