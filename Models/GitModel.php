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

		$address = $this->config['database-address'] . "/" . $this->database . $this->locationOfBag( $id ) . ".json";
		
		if( !file_exists($address) ){

			throw new \Exception("Inexistent Record.");

		}

		$result = file_get_contents( $address );

		$result_parsed = json_decode( $result, true );

		$this->loadObject( $result_parsed );

		return $this;

	}

	/**
	 * @todo find a solution for search
	 * @return Array
	 */
	public function findAll(){

		$result = $this->lsTreeHead( $this->database . '/' );

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
		$result = $this->lsTreeHead( $this->database . '/' );

		$result_complete = [];
		foreach ($result as $key => $record) {
			
			$record->file_content = $this->getFileContent( $record );
			
			array_push($result_complete, $record);

		}

		// prepare the logic
		// TODO: accept different logic conditions
		// $logic_condition = "AND";
		// if( array_search('logic', $param) !== false ){
		//	$logic_condition = $value[array_search('logic', $param)];
		// }

		// filter by the search
		// $result_complete = array_filter($result_complete, function( $item ) use ($param, $value, $logic_condition){
                $result_complete = array_filter($result_complete, function( $item ) use ($param, $value){

                    $found = false;
echo "<pre>";var_dump($item->file_content);exit;
						if( strstr($item->file_content->title, $value) !== false ){
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

        $adapter = new Local( $this->config['database-address'] . '/' . $this->database );

        $filesystem = new Filesystem($adapter);

        $content = json_encode($client_data->content, JSON_PRETTY_PRINT);

        $id = null;

        if( is_null($client_data->id) ){

            $id = $this->nextId();

            $filesystem->write( $id . '.json', $content);

            if( $this->isBag() ){

                $this->createBagForRecord( $id );

            }

            $this->last_inserted_id = $id;

        }else{

            $id = $client_data->id;

            $filesystem->update( $this->locationOfBag( $id ) . '.json', $content);

        }

        $result = $this->saveVersion();
		
        return $id;

    }

	/**
	 * @todo handle exceptions
	 * @param Int $id
	 */
	public function delete( $id ){

		$adapter = new Local( $this->config['database-address'] . '/' . $this->database );

		$filesystem = new Filesystem($adapter);

		if( file_exists($id . '.json') ){

			$filesystem->delete( $id . '.json');

		}else{

			$filesystem->deleteDir( $id );

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

		$command = "ls-tree HEAD -r " . $this->database;

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

		$ls_tree_result = $this->lsTreeHead( $this->database . '/' );

		$ls_tree_result = array_map(function( $item ){
			return $item->id;
		}, $ls_tree_result);

		$next_id = 1;
		if( count($ls_tree_result) > 0 ){
			$next_id = max($ls_tree_result) + 1;
		}

		return $next_id;

	}

	// ------------------------------------------------------------------------
	// PRIVATE
	// ------------------------------------------------------------------------

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

			if( $is_db )
				$new_object->id = preg_replace("/[^\d]/", "", $result[3]);

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
	private function loadObject( Array $data_loaded ){

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

			$id = preg_replace("/[^0-9]/", '', $record->address);

			$location = $record->address . '/data/' . $id . '.json';

		}

		$full_record_addess = $this->config['database-address'] . "/" . $location;

		$content_temp = file_get_contents( $full_record_addess );

		$record->file_content = json_decode($content_temp);

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


}
