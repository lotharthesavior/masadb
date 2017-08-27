<?php

namespace Models\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * The purpose of this code is to organize the Workflow tasks 
 * of Git in the Data Storage.
 * 
 * @author Savio Resende
 */

trait GitWorkflow
{

	/**
	 * 
	 */
	public function saveVersion(){

		// TODO: these 2 steps are taking far too long!!!!
		$result_stage = $this->git->stageChanges();

		$result_commit = $this->git->commitChanges();

		// get the filesystem for the current database
		$local_address = $this->config['database-address'] . '/' . $this->_getDatabaseLocation();
		$filesystem = $this->filesystem->getFileSystemAbstraction( $local_address );

		$this->git->placeMetadata($this->database, $filesystem);

        $this->updateCache();

		return $result_stage && $result_commit;

	}

	/**
	 * 
	 */
	public function saveRecordVersion( $item = null ){
        // get the filesystem for the current database
        $local_address = $this->config['database-address'] . '/' . $this->_getDatabaseLocation();
        $filesystem = $this->filesystem->getFileSystemAbstraction( $local_address );
        
        // Update cache launching a async request
        $this->localAsyncRequest([
            'database' => $this->_getDatabaseLocation()
        ]);

        // Update cache by loading it and placing the new record
        if( 
        	!isset($this->no_cache) 
        	|| (isset($this->no_cache) && $this->no_cache == false)
        ){
        	$this->addItemToCache( $item );
        	// var_dump($item);exit;
        }

        return true;
	}

	/**
	 * Make async request
	 * 
	 * TODO: this method hes to be changed to inside the 
	 *       oauth wall
     * 
     * @param Array $body
     * @return void
	 */
	private function localAsyncRequest($body) {
        $url = "https" . '://' . $this->config['domain'] . "/git-async";

        // $header = [
            // 'ClientId' => $_SERVER['HTTP_CLIENTID'],
            // 'Authorization' => $_SERVER['HTTP_AUTHORIZATION'],
            // 'Content-Type' => $_SERVER['HTTP_CONTENT_TYPE']
        // ];
        
        // \Helpers\AppHelper::curlPostAsync($url, $body, $header);
        \Helpers\AppHelper::curlPostAsync($url, $body);
    }

}