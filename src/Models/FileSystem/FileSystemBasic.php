<?php

/**
 * FileSyste Basic
 * 
 * @author Savio Resende <savio@savioresende.com.br>
 */

namespace Models\FileSystem;

use League\Flysystem\Filesystem;
use League\Flysystem\Config;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Plugin\ListPaths;
use League\Flysystem\Plugin\ListWith;
use League\Flysystem\Plugin\GetWithMetadata;

use Models\Record;
use \Ds\Deque;

class FileSystemBasic implements \Models\Interfaces\FileSystemInterface
{
    /**
     * @param $local_address
     * @return Filesystem
     */
    public function getFileSystemAbstraction( $local_address ){
        $adapter = new Local( $local_address );

        return new Filesystem($adapter, new Config([
            'disable_asserts' => true,
        ]));
    }

    /**
     * Get file content of the record
     * 
     * @param Record $record
     */
    public function getFileContent( Record $record, $is_bag, $database_address = "." ){
        $location = $record->getAddress();

        if( $is_bag ){

            $id = $record->getIdOfAsset( $record->getAddress() );
            
            $location = $record->getAddress() . '/data/' . $id . '.json';

        }
        // var_dump($location);exit;

        $full_record_addess = $location;
        if( !empty($database_address) ) {
            $full_record_addess = $database_address . "/" . $location;
        }

        $content_temp = file_get_contents( $full_record_addess );

        $record->setFileContent((object) json_decode($content_temp));
        
        // get timestamp of file

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

    /**
     * List Records in the working directory
     * 
     * @todo not functional right now
     * @param String $database  - format expected: "{string}/"
     * @return Deque
     */
    public function listWorkingDirectory( $database = '', $is_bag ){
        $is_db = $database != '';

        if( !$is_db )
            return new Deque([]);

        $records = new Deque(scandir($database));
        $records = $records->filter(function( $dir ){
            return $dir != "."
                   && $dir != ".."
                   && $dir != ".git";
        });

        // parse resutls
        $result_deque = $records->map(function( $records_row ) use ($is_db, $is_bag, $database){
            $new_record = new Record;
            $new_record->loadRowStructureSimpleDir( $database, $records_row ); // TODO: this method has changed!
            $new_record = $this->getFileContent( $new_record, $is_bag, "" );
            return $new_record;
        });

        return $result_deque;
    }

    /**
     * Create the Directory to serve as Database
     * 
     * @param string $base_location
     * @param string $database
     * 
     * @return bool
     */
    public function createDatabaseDirectory(string $base_location, string $database): bool
    {
        $filesystem = $this->getFileSystemAbstraction( $base_location );
        return $filesystem->createDir($database);
    }
}