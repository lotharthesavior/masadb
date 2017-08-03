<?php

namespace Models;

use \Git\Coyl\Git;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Plugin\ListPaths;
use League\Flysystem\Plugin\ListWith;
use League\Flysystem\Plugin\GetWithMetadata;

/**
 * 
 * Abstraction for the Model that keeps the data with Git
 * 
 * @author Savio Resende <savio@savioresende.com.br>
 * 
 * Dependency: this model uses the project https://github.com/coyl/git to interact with Git.
 * 
 * Outline:
 * 
 *     1. find
 *     2. findAll
 *     3. save
 *     4. delete
 *     5. lsTreeHead
 *     6. showFile
 *     7. lsTree
 *     8. splitByLine
 *     9. nextId
 *     10. parseLsTree
 *     11. loadObject
 * 
 * @internal This project supports BagIt, this means that, if the model that is extending this abstract
 * class has a Trait "BagUtilities", it will use it and manage the record s bags. For this, there is a 
 * dependency: https://github.com/lotharthesavior/BagItPHP.git
 * 
 */

// ------------------------------------------------------------------------

// -- Primary concepts --

// use \Git\Coyl\Git;

// require_once('Git.php');

// $repo = \Coyl\Git\Git::open('.');
// $repo = \Coyl\Git\Git::open('data');
// -or- Git::create('/path/to/repo')

// echo "<pre>";var_dump($repo);exit;

// code example for the usage of the Git class
// $repo->add('.');
// $repo->commit('Some commit message');
// $repo->push('origin', 'master');

// list all files in the root directory
// $result = $repo->run("ls-tree HEAD");
// var_dump($result);

// list all files in all directories
// git ls-tree --full-tree -r HEAD

// search
// https://git-scm.com/book/en/v2/Git-Tools-Searching
// git grep test
// $result = $repo->run("grep -n test");
// var_dump($result);

// ------------------------------------------------------------------------

abstract class GitModel
{

	// attribute to specify the sorting type: ASC | DESC
	// protected $sortType;

	// ------------------------------------------------------------------------
	// PUBLIC
	// ------------------------------------------------------------------------

	public function __construct(){

		$config_json = file_get_contents("config.json");
		$this->config = json_decode($config_json, true);

		$this->repo = \Coyl\Git\Git::open( $this->config['database-address'] );

	}

	/**
	 * 
	 */
	public function find( $id ){

		$address = $this->config['database-address'] . "/" . $this->getDatabaseLocation() . "/" . $this->locationOfBag( $id ) . ".json";
		
		if( !file_exists($address) ){

			throw new \Exception("Inexistent Record.");

		}

		$result = file_get_contents( $address );

		return $result;

	}

	/**
	 * @todo find a solution for search
	 * @return Array
	 */
	public function findAll(){

		$result = $this->lsTreeHead( $this->getDatabaseLocation() . "/" );

		$result_complete = [];
		foreach ($result as $key => $record) {
			
			$record->file_content = $this->getFileContent( $record );
			
			array_push($result_complete, $record);

		}

		$result_complete = $this->sortResult($result_complete);

		// echo "<pre>";var_dump($result_complete);exit;

		return $result_complete;

	}

	/**
	 * 
	 * @internal Any param with field name 'logic', will be considered
	 *           logic condition for the search
	 * @param String $param || Array $param
	 * @param String $value || Array $value
	 */
	public function search( $param, $value){
		$result = $this->lsTreeHead( $this->getDatabaseLocation() . '/' );

		$result_complete = [];
		foreach ($result as $key => $record) {
			
			$record->file_content = $this->getFileContent( $record );
			
			array_push($result_complete, $record);

		}

        $result_complete = array_filter($result_complete, function( $item ) use ($param, $value){

            $found = false;
            if( 
                isset($item->file_content->{$param})
                && strstr($item->file_content->{$param}, $value) !== false 
            ){
                $found= true;
            }

            return $found;

		});

		return $result_complete;

	}

    /**
     * @param Array $client_data | eg.: ["id" => {int}, "content" => {array}]
     */
    public function save( Array $client_data ){

        $client_data = (object) $client_data;

        $local_address = $this->config['database-address'] . '/' . $this->getDatabaseLocation();

        $adapter = new Local( $local_address );

        $filesystem = new Filesystem($adapter);

        // var_dump($client_data);exit;

        $content = json_encode($client_data->content, JSON_PRETTY_PRINT);

        $id = null;

        if( 
            !isset($client_data->id)
            || is_null($client_data->id) 
        ){

            $id = $this->nextId();

            $item_address = $id . '.json';

            // this method may turn into static
            $this->checkPreExistence( $filesystem,  $item_address );

            $filesystem->write( $item_address, $content);

            if( $this->isBag() ){

                $this->createBagForRecord( $id );

            }

            $this->last_inserted_id = $id;

        }else{

            $id = $client_data->id;

            $item_address = $this->locationOfBag( $id ) . '.json';

            $this->checkPreExistence( $filesystem,  $item_address );

            $filesystem->update( $item_address, $content);

        }

        $result = $this->saveVersion();
		
        return $id;

    }

    /**
     * Check the pre-existence of the item. This method 
     * will identify elements that are not detected by 
     * the working directory verison, in other words, not 
     * commited items
     *
     * @todo send the $result into the log
     * @param Filesystem $filesystem
     * @param String $item_address
     * @return void
     */
    public function checkPreExistence( Filesystem $filesystem,  $item_address ){

        if( $filesystem->has( $item_address ) ){
            $result = $this->saveVersion();
        }

    }

	/**
	 * 
	 * @internal simple registers can be simple json files, but 
	 *           any other type of file, have to be a BagIt.
	 * @param Int $id
	 */
	public function delete( $id ){

		$database_url = $this->config['database-address'] . '/' . $this->getDatabaseLocation();

		$adapter = new Local( $database_url );

		$filesystem = new Filesystem($adapter);


		if( $filesystem->has($id . '.json') ){

			$filesystem->delete( $id . '.json');

		} elseif ( $filesystem->has($id) ){

			$filesystem->deleteDir( $id );

		} else {

			throw new \Exception("Record not found!", 1);

		}

		$result = $this->saveVersion();

		return $result;

	}

	/**
	 * @param String $database  - format expected: "{string}/"
	 */
	public function lsTreeHead( $database = '' ){
		
		$command = 'ls-tree HEAD ' . $database;

		$result = $this->repo->run( $command );

		$is_db = $database != '';

		$result_array_parsed = $this->parseLsTree( $result, $is_db );

		return $result_array_parsed;

	}

	/**
	 * 
	 */
	public function showFile( $file, $branch = "master" ){

		$result = $this->repo->show( $branch . ':' . $file );

		return $result;

	}

	// ------------------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------------------

	/**
	 * 
	 */
	protected function lsTree(){

		$command = "ls-tree HEAD -r " . $this->getDatabaseLocation();

		$cli_result = $this->repo->run($command);

		$result_array_parsed = $this->parseLsTree( $cli_result, true );

		return $result_array_parsed;
	}

	/**
	 * 
	 */
	protected function splitByLine( $string ){

		$array = preg_split ('/$\R?^/m', $string);

		return $array;

	}

	/**
	 * 
	 */
	protected function nextId(){

		$ls_tree_result = $this->lsTreeHead( $this->getDatabaseLocation() . '/' );

		$ls_tree_result = array_map(function( $item ){
			return $item->id;
		}, $ls_tree_result);

		$next_id = 1;
		if( count($ls_tree_result) > 0 ){
			$next_id = intval(max($ls_tree_result)) + intval(1);
		}

		return $next_id;

	}

	// ------------------------------------------------------------------------
	// PRIVATE
	// ------------------------------------------------------------------------

	/**
	 * Analyze the presence of client_id and add it to the database 
	 * folder to keep data into the client scope
	 */
	protected function getDatabaseLocation(){

		$database_location = "";

		if( 
			isset($this->client_id) 
			&& !empty($this->client_id) 
		){
			// var_dump($this->client_id);exit;
			$database_location .= "client_" . $this->client_id[0] . '/';
		}

		$database_location .= $this->database;

		return $database_location;

	}

	/**
	 * Turn the git ls-tree command into Array with
	 * discriminated metadata
	 * 
	 * @param String $cli_result
	 * @param Bool $is_db - here is decided if the parsing will fill id 
	 *                      attribute or not
	 * @return Array
	 */
	private function parseLsTree( $cli_result, $is_db = false ){

		$result_array = $this->splitByLine($cli_result);
		
		$result_array_parsed = array();

		$result_array = array_filter($result_array);


		foreach ($result_array as $key => $value) {
			
			$result = preg_split('/\s+/', $value);

			$result = array_filter($result);

			$new_object = new \stdClass;

			if( $is_db ){
				$new_object->id = $this->getIdOfAsset( $result[3] );
			}

			$new_object->permissions 	= $result[0];
			$new_object->type 			= $result[1];
			$new_object->revision_hash 	= $result[2];
			$new_object->address 		= $result[3];

			array_push($result_array_parsed, $new_object);

		}

		return $result_array_parsed;

	}

	/**
	 * This will load the resultant object into file_content attribute
	 * 
	 * @param Array $data_loaded
	 */
	protected function loadObject( Array $data_loaded ){

		$this->file_content = new \stdClass;

		foreach ($data_loaded as $key => $record) {
			
			$this->file_content->{$key} = $record;

		}

	}

	/**
	 * Verify if the current model is compatible with Bagit
	 * 
	 * @return Boolean
	 */
	function isBag(){

		$is_bag = false;

		if( method_exists($this, 'createBagForRecord') ){

			$is_bag = true;

		}

		return $is_bag;

	}

	/**
	 * Define location for bag
	 * 
	 * @internal the verified method 'createBagForRecord' is from 'BagUtilities' Trait
	 * @param Integer $id
	 */
	private function locationOfBag( $id ){

		$location = '/' . $id;

		if( $this->isBag() ){

			$location = '/' . $id . '/data/' . $id;

		}

		return $location;

	}

	/**
	 * Get file content of the record
	 */
	private function getFileContent( $record ){

		$location = $record->address;

		if( $this->isBag() ){

			$id = $this->getIdOfAsset( $record->address );
			
			$location = $record->address . '/data/' . $id . '.json';

		}

		$full_record_addess = $this->config['database-address'] . "/" . $location;

		$content_temp = file_get_contents( $full_record_addess );

		$record->file_content = (object) json_decode($content_temp);

		// get timestamp of file

			$adapter = new Local( $this->config['database-address'] );

			$filesystem = new Filesystem( $adapter );

			$timestamp = filemtime( $full_record_addess );

			$record->file_content->timestamp = $timestamp;

			$record->file_content->updated_at = gmdate("Y-m-d H:i:s", $timestamp);

		// / get timestamp of file

		return $record->file_content;

	}

	/**
	 * Sort a Collection
	 * 
	 * @todo this function will encapsulate the sorting functions
	 * @todo validate $this->sortType
	 * @param Array $collection
	 */
	private function sortResult( $collection ){

		$sort_type = "ASC";
		if( 
			isset($this->sortType) 
			&& !empty($this->sortType)
		){
			$sort_type = $this->sortType;
		}

		switch ( $sort_type ) {

			case 'ASC':
				$collection = $this->sortAscendingOrder( $collection );
				break;

			case 'creation_DESC':
				$collection = $this->sortCreationDescendingOrder( $collection );
				break;

		}

		return $collection;

	}

	/**
	 * Sort Ascending
	 * 
	 * @param Array $collection
	 */
	private function sortAscendingOrder( $collection ){

		usort($collection, function($a, $b){
			return (int) $a->id > (int) $b->id;
		});

		return $collection;

	}

	/**
	 * Sort Ascending
	 * 
	 * @param Array $collection
	 */
	private function sortCreationDescendingOrder( $collection ){

		usort($collection, function($a, $b){
			return (int) $b->file_content->timestamp > (int) $a->file_content->timestamp;
		});

		return $collection;

	}

	/**
	 * Return the Id of the physical address
	 * 
	 * @return Int
	 */
	private function getIdOfAsset( $address ){

		$address_exploded = explode('/', $address);

		$asset_name = end($address_exploded);

		$id = preg_replace("/[^\d]/", "", $asset_name);

		return $id;

	}

}
