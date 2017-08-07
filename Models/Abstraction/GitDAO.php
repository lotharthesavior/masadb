<?php

namespace Models\Abstraction;

use \Git\Coyl\Git;

/**
 * 
 * Abstraction for the Model that keeps the data with Git
 * 
 * @author Savio Resende <savio@savioresende.com.br>
 * 
 */

abstract class GitDAO implements \Models\Interfaces\GitDAOInterface
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
	){
		$config_json = file_get_contents("config.json");
		$this->config = json_decode($config_json, true);
		
		$this->filesystem = $filesystem;
		$this->git = $git;
		$this->git->setRepo( $this->config['database-address'] );

		$this->bag = $bag;
	}

	/**
	 * Search for a Single Record by the id
	 * 
	 * @param int $id
	 * @return Array
	 */
	public function find( $id ){
		$address = $this->config['database-address'] . "/" . $this->_getDatabaseLocation() . "/" . $this->bag->locationOfBag( $id, $this->isBag() ) . ".json";
		
		if( !file_exists($address) ){

			throw new \Exception("Inexistent Record.");

		}

		$result = file_get_contents( $address );

		return $result;
	}

	/**
	 * Find all the Records in the database
	 * 
	 * @return \Ds\Deque
	 */
	public function findAll(){

		$result_complete = $this->getAllRecords();

		$result_complete = $this->_sortResult($result_complete);

		return $result_complete;

	}

	/**
	 * @return mix
	 */
	private function getAllRecords( $format = "Array" ){
		$result = new \Ds\Vector($this->git->lsTreeHead( 
			$this->_getDatabaseLocation() . '/', 
			$this->filesystem, 
			$this->isBag(),
			$this->config['database-address'] 
		));

		return $result;
	}

	/**
	 * Search for a single param
	 * 
	 * @internal Any param with field name 'logic', will be considered
	 *           logic condition for the search
	 * @param String $param || Array $param
	 * @param String $value || Array $value
	 */
	public function search( $param, $value ){
		$result_complete = $this->getAllRecords();

		$result_complete = $result_complete->filter(function( $record ) use ($param, $value){
			if( $param != "id" ) return $record->stringMatch( $param, $value );
            if( $param == "id" && $record->valueEqual( $param, $value ) ) return false;
		});

		return $result_complete;
	}

	/**
	 * Search that works with multiple params
	 * 
	 * @param Array $params
	 */
	public function searchRecord( $params, $logic = [] ){
		$result_complete = $this->getAllRecords();

		$result_complete = $result_complete->filter(function( $record ) use ($params){
			return $record->multipleParamsMatch( $params );
		});

		return $result_complete;
	}

    /**
     * @param Array $client_data | eg.: ["id" => {int}, "content" => {array}]
     */
    public function save( Array $client_data ){

        $client_data = (object) $client_data;

        $local_address = $this->config['database-address'] . '/' . $this->_getDatabaseLocation();

        // League\Flysystem\Filesystem
        $filesystem = $this->filesystem->getFileSystemAbstraction( $local_address );

        $content = json_encode($client_data->content, JSON_PRETTY_PRINT);

        $id = null;

        if( 
            !isset($client_data->id)
            || is_null($client_data->id) 
        ){

            $id = $this->_nextId();

            $item_address = $id . '.json';

            if( $filesystem->has( $item_address ) )
            	$this->saveVersion();

            $filesystem->write( $item_address, $content);

            if( $this->isBag() ){

                $this->createBagForRecord( $id );

            }

            $this->last_inserted_id = $id;

        }else{

            $id = $client_data->id;

            $item_address = $this->bag->locationOfBag( $id, $this->isBag() ) . '.json';

            if( $filesystem->has( $item_address ) )
            	$this->saveVersion();

            $filesystem->update( $item_address, $content);

        }

        $result = $this->saveVersion();
		
        return $id;

    }

	/**
	 * 
	 * @internal simple registers can be simple json files, but 
	 *           any other type of file, have to be a BagIt.
	 * @param Int $id
	 */
	public function delete( $id ){

		$database_url = $this->config['database-address'] . '/' . $this->_getDatabaseLocation();

		// League\Flysystem\Filesystem
		$filesystem = $this->filesystem->getFileSystemAbstraction( $database_url );

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
	 * Analyze the database to get the next id
	 * 
	 * 
	 * @return int
	 */
	protected function _nextId(){

		$ls_tree_result = $this->git->lsTreeHead( 
			$this->_getDatabaseLocation() . '/', 
			$this->filesystem, 
			$this->isBag(),
			$this->config['database-address']
		);

		if( $ls_tree_result->count() < 1 )
			return 1;

		$ls_tree_result = $ls_tree_result->map(function($record){
			return (int) $record->getId();
		});

		$ls_tree_result->sort();

		return $ls_tree_result->last() + 1;
	}

	/**
	 * Analyze the presence of client_id and add it to the database 
	 * folder to keep data into the client scope
	 * 
	 * @return String $database_location
	 */
	protected function _getDatabaseLocation(){
		$database_location = "";

		if( isset($this->client_id) && !empty($this->client_id) )
			$database_location .= "client_" . $this->client_id[0] . '/';

		$database_location .= $this->database;

		return $database_location;
	}

	/**
	 * Verify if the current model is compatible with Bagit
	 * 
	 * @return Boolean
	 */
	public function isBag(){

		$is_bag = false;

		if( method_exists($this, 'createBagForRecord') ){

			$is_bag = true;

		}

		return $is_bag;

	}

	/**
	 * Sort a Collection
	 * 
	 * @todo this function will encapsulate the sorting functions
	 * @todo validate $this->sortType
	 * @param Array $collection
	 */
	private function _sortResult( $collection ){

		$sort_type = "ASC";
		if( 
			isset($this->sortType) 
			&& !empty($this->sortType)
		){
			$sort_type = $this->sortType;
		}


		switch ( $sort_type ) {

			case 'ASC':
				$collection->sort(function($a, $b){
					return (int) $a->getId() > (int) $b->getId();
				});
				break;

			case 'creation_DESC':
				$collection->sort(function($a, $b){
					return (int) $a->getId() < (int) $b->getId();
				});
				break;

		}

		return $collection;

	}

	/**
	 * Sort Ascending
	 * 
	 * @param Array $collection
	 */
	private function _sortAscendingOrder( $collection ){

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
	private function _sortCreationDescendingOrder( $collection ){

		usort($collection, function($a, $b){
			return (int) $b->getFileTimestamp() > (int) $a->getFileTimestamp();
		});

		return $collection;

	}

}
