<?php

namespace Helpers;

require __DIR__ . '/../../vendor/autoload.php';

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use \Lotharthesavior\BagItPHP\BagIt;
use \Ds\Deque;
use \Models\Record;

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
	public function getCacheData( $client, $database, $search = "all", $create_cache = false ){
		$root_path_cache = getcwd() . '/cache/client_' . $client . '/';
		$root_path_database = getcwd() . '/data/client_' . $client . '/';
        $full_database_address = $root_path_database . $database;

		// get the cache timestamp AND check the existence of the cache ---
			$filesystem = $this->getFileSystem( $root_path_cache );
			
			$database_cache_path = $database;
			if( $search == "all" ) {
				$database_cache_path .= "/all";
			}

			if( !$filesystem->has($database_cache_path) ) {
                return false;
			}
            
            $cache_filestamp = $this->getTimeOfFileSystem($filesystem, $database_cache_path);
        // ------------------------------------

        $config = config();

        // get the database timestamp ---
            // $filesystem2 = $this->getFileSystem( $root_path_database );

            // this is commented as its validation in a few lines because the validation has to be able
            // to happen through git
            // $directory_filestamp = $this->getTimeOfFileSystem( $filesystem2, $database );
            $git_filestamp = $this->getTimeOfGitVersion( $full_database_address );
        // ------------------------------------
		
		$cache_deque = unserialize($filesystem->read($database_cache_path));

        $this->data = $cache_deque;

        // -- async call --
        // TODO: rebuild this part
        // if( 
        //     $cache_filestamp < $git_filestamp 
        //     && $create_cache
        // ){
        //     $header = [
        //         'ClientId' => $_SERVER['HTTP_CLIENTID'],
        //         'Authorization' => $_SERVER['HTTP_AUTHORIZATION'],
        //         'Content-Type' => $_SERVER['HTTP_CONTENT_TYPE']
        //     ];
        //     \Helpers\AppHelper::curlPostAsync($url, $body, $header);
        //     \Helpers\AppHelper::curlPostAsync(
        //         $config['protocol'] . '://' . $config['domain'] . "/git-async", 
        //         [ 'database' => $full_database_address ]
        //     );
        // }
        // --

		return $cache_deque;
	}

	/**
	 * The purpose of this method is to get the timestamp, 
	 * and, in the future replace this to something that is 
	 * more reliable in case the UNIX timestamp is replaced
	 * 
	 * @param Filesystem $filesystem
	 * @param String $path
	 * @return Int (UNIX Timestamp)
	 */
	private function getTimeOfFileSystem( Filesystem $filesystem, $path ){
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
	private function getAllPhysicalRecords( $client, $database, $database_full_address = '' ){
		$contents = new Deque(scandir($database_full_address));
		$contents = $contents->filter(function( $dir ){
            return $dir != "."
                && $dir != ".."
                && $dir != ".git";
        });
		
		$contents->map(function($path) use ($client, $database) {
			return $this->buildRecordFromPath( $path, $client, $database );
		});

		return $contents;
	}

	/**
	 * This method retrieve the root path of a specific client/database
	 * 
	 * @param String $client
	 * @param String $database
	 * @return String
	 */
	private function getRootPath( $client, $database ){
		return getcwd() . '/data/client_' . $client . '/' . $database . '/';
	}

	/**
	 * This method build the records according to the path
	 * 
	 * @param String $path
	 * @param Int $client
	 * @return Array
	 */
	public function buildRecordFromPath( $path, $client, $database ){
		$root_path = $this->getRootPath($client, $database);

		$record_instance = new Record;

		// avoid 2 bars together
        if( 
			$root_path[strlen($root_path) - 1] == "/" 
			&& $path[0] == "/"
		) {
			$path = substr($path, 1);
        }

		$data_path = $root_path . $path . "/data/";

		// avoid file inside an existent bag
		$path_for_bag = $path;
		if( file_exists($root_path . $path) ){
			// get the id - the first element after the database name

			// TODO: check this case, it was removed because it was creating a bag from the root of the database directory
			// $path_for_bag = explode($path_for_bag, $data_path)[0] . $path_for_bag;
			
			$path_for_bag = $root_path . $path;
		}

        $record_instance->loadRowStructureSimpleDir( $root_path, $path );

        if (file_exists($data_path)) {
            $bag = new BagIt($path_for_bag);
        }

        if( isset($bag) && (bool)$bag->isValid() ){

            $data_filesystem = $this->getFileSystem($data_path);
            $data_contents = $data_filesystem->listContents("", true);
        
            foreach ($data_contents as $key => $_file)
                $record_instance->setFileContent( (array) json_decode(file_get_contents($data_path . $_file['path'])) );

        }else{

            $record_instance->setFileContent( (array) json_decode(file_get_contents($root_path . $path)) );
            
        }

		return $record_instance;
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
		if ($this->data === null) {
			$this->data = new \Ds\Deque();
		}

		return $this->data;
	}

	/**
	 * @param Int $client
	 * @param String $database
	 * @return $this
	 */
	public function getAllRecords( $client, $database, $database_full_address = '' ){
		$records = $this->getAllPhysicalRecords( $client, $database, $database_full_address );
		
		$this->setData($records);

		return $this;
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
    	$filesystem = $this->getFileSystem(__DIR__ . '/../../');

    	if( !$filesystem->has("cache") )
			$filesystem->createDir("cache");

		$cache_dir = "cache/". $database;
		if( !$filesystem->has($cache_dir) )
			$filesystem->createDir($cache_dir);

    	$filesystem->put( $cache_dir . "/all", serialize($this->data) );
    }

    /**
     * This method merge the new record with the current data
     * 
     * @param Record $new_record
     * @return mix (current data after merge || false)
     */
    public function merge( Record $new_record ){
    	$this->data->push($new_record);
        return $this->data;
    }

}