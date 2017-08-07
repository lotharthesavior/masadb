<?php

namespace Models\Git;

class GitBasic implements \Models\Interfaces\GitInterface
{
	// keep the Git instance for interactions
	protected $repo;

	/**
	 * @param \Coyl\Git\Git $repo
	 */
	public function __constructor( $database_address = null ){
		if( $database_address )
			$this->setRepo($database_address);
	}

	/**
	 * Prepare the repository for the job
	 * 
	 * @internal this method is necessary because the instance is 
	 *           created before the address is available. This is
	 *           happens for the possibility of Polymorphism.
	 * @param String $database_address
	 * @return void
	 */
	public function setRepo( $database_address ){
		$this->repo = \Coyl\Git\Git::open( $database_address );
	}

	/**
	 * @internal depends on $this->repo
	 * @param String $database  - format expected: "{string}/"
	 * @return \Ds\Deque
	 */
	public function lsTreeHead( $database = '', \Models\Interfaces\FileSystemInterface $filesystem, $is_bag, $database_address ){
		$this->checkRepo();

		$command = 'ls-tree HEAD ' . $database;

		$result = $this->repo->run( $command );

		$is_db = $database != '';

		return $this->parseLsTree( $result, $is_db, $filesystem, $is_bag, $database_address );
	}

	/**
	 * Turn the git ls-tree command into Array with
	 * discriminated metadata
	 * 
	 * @internal the $cli_result param "row" is expected to be like this: 
	 *               structure1: "100644 blob 0672e3d1ca4498ea4f6de663764e28f712468b03	oauth/access_token/1.json"
	 * @param String $cli_result
	 * @param Bool $is_db - here is decided if the parsing will fill id 
	 *                      attribute or not
	 * @param \Models\Interfaces\FileSystemInterface $filesystem
	 * @param Bool $is_bag
	 * @return \Ds\Deque
	 */
	public function parseLsTree( $cli_result, $is_db = false, \Models\Interfaces\FileSystemInterface $filesystem, $is_bag, $database_address ){

		$result_array = \Helpers\AppHelper::splitByLine($cli_result);
		$result_array = array_filter($result_array);

		$result_deque = new \Ds\Deque($result_array);
		
		$result_deque = $result_deque->map(function( $records_row ) use ($is_db, $filesystem, $is_bag, $database_address){
			$new_record = new \Models\Record;
			$new_record->loadRowStructure1( $records_row, $is_db );
			$new_record = $filesystem->getFileContent( $new_record, $is_bag, $database_address );
			return $new_record;
		});

		return $result_deque;
	}

	/**
	 * Wrapper for git show command
	 * 
	 * @internal depends on $this->repo
	 * @param String $file
	 * @param String $branch
	 * @return String - command line result
	 */
	public function showFile( $file, $branch = "master" ){
		$this->checkRepo();

		$result = $this->repo->show( $branch . ':' . $file );

		return $result;

	}

	/**
	 * Check if the Repository is started.
	 * 
	 * @return void
	 */
	private function checkRepo(){
		if( !isset($this->repo) || empty($this->repo) )
			throw new Exception("No Repository started.");
	}

	/**
	 * Execute git cli add
	 * 
	 * @todo analyze the result
	 */
	public function stageChanges(){

		$this->repo->add();

		return true;

	}

	/**
	 * Execute git cli commit
	 * 
	 * @todo analyze the result
	 * @return bool
	 */
	public function commitChanges(){

		$message = "Commit from Masa manager - " . date("Y-d-m H:i:s") . ". - by Savio";

		$this->repo->commit( $message );

		return true;

	}
}