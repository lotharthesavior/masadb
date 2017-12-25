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
		try {
			$this->repo = \Coyl\Git\Git::open( $database_address );
		} catch (GitException $e) {
			throw $e;
		}
	}

	/**
	 * @internal depends on $this->repo
	 * @param String $database  - format expected: "{string}/"
	 * @return \Ds\Deque
	 */
	public function lsTreeHead( $database = '', \Models\Interfaces\FileSystemInterface $filesystem, $is_bag, $database_address ){
		$this->checkRepo();
		// var_dump($database_address);exit;

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
	public function stageChanges( $item = null ){

		if( !is_null($item) )
			$result = $this->repo->add($item);
		else
			$result = $this->repo->add();

		return true;

	}

	/**
	 * Execute git cli commit
	 * 
	 * @todo analyze the result
	 * @return bool
	 */
	public function commitChanges(){
		$message = "Commit from Masa - " . date("Y-d-m H:i:s") . ".";

		$this->repo->commit( $message );

		return true;
	}

	/**
	 * Get the last version timestamp for cache purpose
	 */
	public function getLastVersionTimestamp(){
		return $this->repo->logFormatted("%at", "", "1");
	}

	/**
	 * @internal for metadata spec, see @prepareMetadata method.
	 */
	public function placeMetadata($database, \League\Flysystem\Filesystem $filesystem){
		$note_message = "";

		$metadata_json = $this->prepareMetadata($database, $filesystem);

		return $this->repo->run("notes add -f -m '" . $metadata_json . "'");
	}

	/**
	 * 
	 */
	public function getMetadata(){
		try {
			$return = $this->repo->run("notes show");
		} catch (\Exception $e) {
			$return = $e->getMessage();
		}

		return $return;
	}

	/**
	 * Metadata:
	 * 1. total number of records
	 * 2. last ID
	 */
	public function prepareMetadata($database, \League\Flysystem\Filesystem $filesystem){
		$current_metadata = $this->getMetadata();
		
		if( strpos($current_metadata, "error") != -1 )
			return $this->generateMetadata($database, $filesystem);

		return $current_metadata;
	}

	/**
	 * @internal for metadata spec, see @prepareMetadata method.
	 */
	public function generateMetadata($database, \League\Flysystem\Filesystem $filesystem){
		$metadata = new \stdClass;

		$filesystem_report = new \Ds\Deque($filesystem->listContents("/"));

		$filesystem_report->sort(function($a, $b){
			return (int) $a['filename'] > (int) $b['filename'];
		});

		$metadata->total_records = $filesystem_report->count();
		$metadata->last_id = ((object) $filesystem_report->last())->filename;

		return json_encode($metadata);
	}

	/**
	 * Init the Repository
	 * 
	 * @param string $repository_address
	 * 
	 * @return void
	 */
	public function initRepository(string $repository_address){
		$this->repo = \Coyl\Git\GitRepo::create($repository_address);
	}
}