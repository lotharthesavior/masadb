<?php

namespace Models\Interfaces;

interface FileSystemInterface
{
	/**
	 * @param String $local_address
	 * @return League\Flysystem\Filesystem
	 */
	public function getFileSystemAbstraction( $local_address );

	/**
	 * Get file content of the record
	 * 
	 * @param Record $record
	 */
	public function getFileContent( \Models\Record $record , $is_bag, $database_address );

	/**
	 * This will load the resultant object into file_content attribute
	 * 
	 * @param Array $data_loaded
	 * @return stdClass
	 */
	public function loadFileObject( Array $data_loaded );
	
}