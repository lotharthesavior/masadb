<?php

require __DIR__ . '/../vendor/autoload.php';

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

use \Lotharthesavior\BagItPHP\BagIt;

/**
 * 
 */
class CacheControl
{

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
	private function getAllPhysicalRecords(){
		$root_path = getcwd() . '/data/client_1/test/';

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
	 * 
	 */
	public function getAllRecords(){
		$records = $this->getAllPhysicalRecords();
		
		$this->data = $records;
	}

	/**
	 * 
	 */
	public function jsonSerialize() {
        return json_encode($this->data);
    }

    /**
     * @return void
     */
    public function persistCache(){
    	$filesystem = $this->getFileSystem(__DIR__.'/../');

    	if( !$filesystem->has("cache") )
			$filesystem->createDir("cache");

    	$filesystem->put( "cache/results", json_encode($this) );
    }

}

$cache_control = new CacheControl;
$cache_control->getAllRecords();
// var_dump(json_encode($cache_control));exit;
$cache_control->persistCache();
// var_dump(json_encode($cache_control));exit;