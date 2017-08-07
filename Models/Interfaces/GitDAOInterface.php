<?php

namespace Models\Interfaces;

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
	 * @param \Models\Interfaces\FileSystemInterface $filesystem
	 * @param \Models\Interfaces\GitInterface $git
	 * @param \Models\Interfaces\BagInterface $bag
	 */
	public function __construct( 
		\Models\Interfaces\FileSystemInterface $filesystem,
		\Models\Interfaces\GitInterface $git,
		\Models\Interfaces\BagInterface $bag
	);

	/**
	 * Search for a Single Record by the id
	 * 
	 * @param int $id
	 * @return Array
	 */
	public function find( $id );

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
	 * @param Array $params
	 */
	public function searchRecord( $params, $logic = [] );

    /**
     * @param Array $client_data | eg.: ["id" => {int}, "content" => {array}]
     */
    public function save( Array $client_data );

	/**
	 * 
	 * @internal simple registers can be simple json files, but 
	 *           any other type of file, have to be a BagIt.
	 * @param Int $id
	 */
	public function delete( $id );

	/**
	 * Verify if the current model is compatible with Bagit
	 * 
	 * @return Boolean
	 */
	public function isBag();
}