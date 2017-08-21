<?php

namespace Models\FileSystem;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Plugin\ListPaths;
use League\Flysystem\Plugin\ListWith;
use League\Flysystem\Plugin\GetWithMetadata;

class FileSystemBasic implements \Models\Interfaces\FileSystemInterface
{
	/**
	 * @param $local_address
	 * @return League\Flysystem\Filesystem
	 */
	public function getFileSystemAbstraction( $local_address ){
		$adapter = new Local( $local_address );

        return new Filesystem($adapter);
	}

	/**
	 * Get file content of the record
	 * 
	 * @param Record $record
	 */
	public function getFileContent( \Models\Record $record, $is_bag, $database_address ){
		$location = $record->getAddress();

		if( $is_bag ){

			$id = $record->getIdOfAsset( $record->getAddress() );
			
			$location = $record->getAddress() . '/data/' . $id . '.json';

		}
		// var_dump($location);exit;

		$full_record_addess = $database_address . "/" . $location;
		// var_dump($full_record_addess);//exit;

		$content_temp = file_get_contents( $full_record_addess );

		$record->setFileContent((object) json_decode($content_temp));

		// get timestamp of file

			// League\Flysystem\Filesystem
			$filesystem = $this->getFileSystemAbstraction( $database_address );

			$timestamp = filemtime( $full_record_addess );

			$record->setFileTimestamp( $timestamp );

			$record->setFileUpdatedAt( gmdate("Y-m-d H:i:s", $timestamp) );

		// / get timestamp of file

		return $record;

	}

	/**
	 * This will load the resultant object into file_content attribute
	 * 
	 * @param Array $data_loaded
	 * @return stdClass
	 */
	public function loadFileObject( Array $data_loaded ){
		$file_content = new \stdClass;

		foreach ($data_loaded as $key => $record) {
			
			$file_content->{$key} = $record;

		}

		return $file_content;
	}

}