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
	 * @param string $database_address
	 * 
	 * @return void
	 */
	public function setRepo(string $database_address);

	/**
	 * @param string $database  - format expected: "{string}/"
	 */
	public function lsTreeHead(string $database = '', \Models\Interfaces\FileSystemInterface $file_system, bool $is_bag, string $database_address);

	/**
	 * Turn the git ls-tree command into Array with
	 * discriminated metadata
	 * 
	 * @internal the $cli_result param "row" is expected to be like this: 
	 *               structure1: "100644 blob 0672e3d1ca4498ea4f6de663764e28f712468b03	oauth/access_token/1.json"
	 * 
	 * @param string $cli_result
	 * @param Bool $is_db - here is decided if the parsing will fill id 
	 *                      attribute or not
	 * @param \Models\Interfaces\FileSystemInterface $file_system
	 * @param bool $is_bag
	 * @param string $database_address
	 * 
	 * @return \Ds\Deque
	 */
	public function parseLsTree(string $cli_result, bool $is_db = false, \Models\Interfaces\FileSystemInterface $file_system, bool $is_bag, string $database_address);

	/**
	 * Wrapper for git show command
	 * 
	 * @param string $file
	 * @param string $branch
	 * 
	 * @return string - command line result
	 */
	public function showFile(string $file, string $branch = "master");

	/**
	 * Execute git cli add
	 * 
	 * @param string $item
	 */
	public function stageChanges(string $item = null);

	/**
	 * Execute git cli commit
	 * 
	 * @return bool
	 */
	public function commitChanges();
}