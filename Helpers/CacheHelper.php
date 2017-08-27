<?php

namespace Helpers;

require __DIR__ . '/../vendor/autoload.php';

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

use \Lotharthesavior\BagItPHP\BagIt;

/**
 * 
 */
class CacheHelper
{
	protected $data;

	protected $filesystem;

	/**
	 * Return cache if it is newer than the database itself
	 * 
	 * @param int $client
	 * @param String $database
	 * @param String $search
	 * @param bool $create_cache - create cache after the current request
	 * @return mix (JSON | Bool)
	 */
	public function getCacheData( $client, $database, $search = "all" ){
		$root_path_cache = getcwd() . '/cache/client_' . $client . '/';
		$root_path_database = getcwd() . '/data/client_' . $client . '/';
        $full_database_address = $root_path_database . $database;

		// get the cache timestamp AND check the existence of the cache ---
			$filesystem = $this->getFileSystem( $root_path_cache );
			
			$database_cache_path = $database;
			if( $search == "all" )
				$database_cache_path .= "/all";

			if( !$filesystem->has($database_cache_path) )
				return false;

			$cache_filestamp = $this->getTimeOfFileSystem($filesystem, $database_cache_path);
		// ------------------------------------

		// get the database tiemstamp ---
			$filesystem2 = $this->getFileSystem( $root_path_database );

			$directory_filestamp = $this->getTimeOfFileSystem($filesystem2, $database);
		// ------------------------------------

		if( $cache_filestamp > $directory_filestamp ){
			$cache_deque = unserialize($filesystem->read($database_cache_path));

			return $cache_deque;
		}

		return false;
	}

	/**
	 * The purpose of this method is to get the timestamp, 
	 * and, in the future replace this to something that is 
	 * more reliable in case the UNIX timestamp is replaced
	 * 
	 * @param \League\Flysystem\Filesystem $filesystem
	 * @param String $path
	 * @return Int (UNIX Timestamp)
	 */
	private function getTimeOfFileSystem( \League\Flysystem\Filesystem $filesystem, $path ){
		return $filesystem->getTimestamp($path);
	}

    /**
     * The purpose of this method is to get the timestamp, 
     * and, in the future replace this to something that is 
     * more reliable in case the UNIX timestamp is replaced
     * 
     * @param String $full_database_address
     * @return Int (UNIX Timestamp)
     */
    private function getTimeOfGitVersion( $full_database_address ){
        $git_basic = new \Models\Git\GitBasic;
        $git_basic->setRepo( $full_database_address );
        return $git_basic->getLastVersionTimestamp();
    }

	/**
	 * 
	 */
	private function getFileSystem( $directory ){
		$adapter = new Local($directory);
		$filesystem = new Filesystem($adapter);

		return $filesystem;
	}

	/**
	 * 
	 * @return Array $contents
	 */
	private function getAllPhysicalRecords( $client, $database ){
		$root_path = getcwd() . '/data/client_' . $client . '/' . $database . '/';

			// var_dump($root_path);exit;
		$filesystem = $this->getFileSystem($root_path);

		$contents = new \Ds\Vector($filesystem->listContents());
		
		$contents->map(function($path) use ($filesystem, $root_path){

			$path['data'] = [];
			$bag = new BagIt( $root_path . $path['path'] );

			if( (bool)$bag->isValid() ){

				$data_path = $root_path . $path['path'] . "/data/";
				$data_filesystem = $this->getFileSystem($data_path);
				$data_contents = $data_filesystem->listContents("", true);
			
				foreach ($data_contents as $key => $_file) {
					array_push($path['data'], file_get_contents($data_path . $_file['path']));
				}

			}else{

				array_push($path['data'], file_get_contents($root_path . $path['path']));
				
			}

			return $path;

		});

		return $contents;
	}

	/**
	 * @param $data_value
	 * @return void
	 */
	public function setData( $data_value ){
		$this->data = $data_value;
	}

	/**
	 * @return \Ds\Deque
	 */
	public function getData(){
		return $this->data;
	}

	/**
	 * @param Int $client
	 * @param String $database
	 * @return $this
	 */
	public function getAllRecords( $client, $database ){
		$records = $this->getAllPhysicalRecords( $client, $database );
		
		$this->setData($records);
	}

	/**
	 * 
	 */
	public function jsonSerialize() {
        return serialize($this->data);
    }

    /**
     * @param String $database - this is the database address inside 
     *                           the "cache" directory, eg.: /client_1/users
     * @return void
     */
    public function persistCache( $database ){
    	$filesystem = $this->getFileSystem(__DIR__.'/../');

    	if( !$filesystem->has("cache") )
			$filesystem->createDir("cache");

		$cache_dir = "cache/". $database;
		if( !$filesystem->has($cache_dir) )
			$filesystem->createDir($cache_dir);

    	$filesystem->put( $cache_dir . "/all", serialize($this->data) );
    }

}

// $cache_control = new CacheControl;
// $cache_control->getAllRecords(1, 'test');
// var_dump(json_encode($cache_control));exit;
// $cache_control->persistCache();
// var_dump(json_encode($cache_control));exit;