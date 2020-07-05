<?php

namespace Models\Interfaces;

use \Models\Interfaces\FileSystemInterface;
use \Models\Interfaces\GitInterface;
use \Models\Interfaces\BagInterface;

interface GitDAOInterface
{
	// Core instance for FileSystem interaction
	// proteced filesystem;

	// Core instance for Git interaction
	// proteced git;

	// Core instance for Bag interaction
	// proteced bag;

	// keep the config.json content parsed
	// protected $config;

	// attribute to specify the sorting type: ASC | DESC
	// protected $sortType;

	/**
	 * @param FileSystemInterface $filesystem
	 * @param GitInterface $git
	 * @param BagInterface $bag
     * @param array $config
	 */
	public function __construct( 
		FileSystemInterface $filesystem,
		GitInterface $git,
		BagInterface $bag,
        array $config = []
	);

	/**
	 * Search for a Single Record by the id
	 * 
	 * @param int $id
	 * @return Array
	 */
	public function find( int $id );

	/**
	 * @todo find a solution for search
	 * @return Array
	 */
	public function findAll();

	/**
	 * Search for a single param
	 * 
	 * @internal Any param with field name 'logic', will be considered
	 *           logic condition for the search
	 * @param String $param || Array $param
	 * @param String $value || Array $value
	 */
	public function search( $param, $value );

	/**
	 * Search that works with multiple params
	 * 
	 * @param array $params
	 */
	public function searchRecord( array $params, $logic = [] );

    /**
     * @param array $client_data | eg.: ["id" => {int}, "content" => {array}]
     */
    public function save( array $client_data );

	/**
	 * 
	 * @internal simple registers can be simple json files, but 
	 *           any other type of file, have to be a BagIt.
	 * @param int|string $id
	 */
	public function delete( $id );

	/**
	 * Verify if the current model is compatible with Bagit
	 * 
	 * @return bool
	 */
	public function isBag();
}