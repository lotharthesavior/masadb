<?php

namespace Models;

use \Git\Coyl\Git;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Plugin\ListPaths;

abstract class GitModel
{

	// ------------------------------------------------------------------------
	// PUBLIC
	// ------------------------------------------------------------------------

	public function __construct(){

		$this->repo = \Coyl\Git\Git::open('data');

	}

	/**
	 * 
	 */
	public function find( $id ){

		$result = file_get_contents( "data/" . $this->database . "/" . $id . ".json" );

		return json_decode($result);

	}

	/**
	 * 
	 */
	public function findAll(){

		$result = $this->lsTree();

		$result_complete = [];
		foreach ($result as $key => $value) {
			
			$content_temp = file_get_contents( "data/" . $value->address );
			$value->file_content = json_decode($content_temp);
			
			array_push($result_complete, $value);

		}

		return $result_complete;

	}

	/**
	 * @param Array $client_data | eg.: ["id" => {int}, "content" => {array}]
	 */
	public function save( Array $client_data ){

		$client_data = (object) $client_data;

		$adapter = new Local('data/' . $this->database);

		$filesystem = new Filesystem($adapter);

		$content = json_encode($client_data->content, JSON_PRETTY_PRINT);

		if( is_null($client_data->id) ){

			$filesystem->write( $this->nextId() . '.json', $content);

		}else{

			$filesystem->update( $client_data->id . '.json', $content);

		}

		$result = $this->saveVersion();

		echo $content;

		exit;

	}

	/**
	 * 
	 */
	public function delete(){

	}

	/**
	 * 
	 */
	public function lsTreeHead(){

		$result = $this->repo->run('ls-tree HEAD');

		$result_array_parsed = $this->parseLsTree( $result );

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

		$ls_tree_result = $this->lsTree();

		return count($ls_tree_result) + 1;

	}

	// ------------------------------------------------------------------------
	// PRIVATE
	// ------------------------------------------------------------------------

	/**
	 * Turn the git ls-tree command into Array with
	 * discriminated metadata
	 * 
	 * @param String $cli_result
	 * @param Bool $is_db
	 * @return Array
	 */
	private function parseLsTree( $cli_result, $is_db = false ){

		$result_array = $this->splitByLine($cli_result);
		
		$result_array_parsed = array();

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

}

// ------------------------------------------------------------------------

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