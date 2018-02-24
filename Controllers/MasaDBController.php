<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Models\Generic;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Plugin\ListPaths;
use League\Flysystem\Plugin\ListWith;
use League\Flysystem\Plugin\GetWithMetadata;

use \Models\Exceptions\NotExistentDatabaseException;

class MasaDBController extends Abstraction\MasaController
{
	
	use \Controllers\traits\commonController;

	protected $container;

    /**
     * Start the controller instantiating the Slim Container
     * 
     * @todo move this to a controller parent class
     */
    public function __construct($container){
        $this->container = $container;
    }

	/**
	 * Fetch All Records
	 * 
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
     *
	 * @param array $args
	 */
	public function getFullCollection (ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        return $this->searchRecords($request, $response, $args);
	}

	/**
	 * Get a Single Record
	 * 
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
     *
	 * @param array $args
	 */
	public function getGeneric (ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
	    $args['key']   = 'id';
	    $args['value'] = $args['id'];

	    unset($args['id']);

	 	return $this->searchRecords($request, $response, $args);
	}

	/**
	 * Search Records
	 * 
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param array $args | ['field' => string, 'value' => string]
	 */
	public function searchRecords (ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $logic = [];

	 	$generic_model = new Generic(
	 		// \Models\Interfaces\FileSystemInterface 
            new \Models\FileSystem\FileSystemBasic,
            // \Models\Interfaces\GitInterface
            new \Models\Git\GitBasic,
            // \Models\Interfaces\BagInterface
            new \Models\Bag\BagBasic
	 	);

        // this part is to be improved, right now the simple 
        // presence will change all comparisons to OR
        $post_data = [];
        if (isset($args['key']) && isset($args['value'])) {
            $post_data[$args['key']] = $args['value'];
        }

        if (isset($post_data['logic'])) {
            $logic = $post_data['logic'];
            unset($post_data['logic']);
        }

        $current_client_id = $request->getHeader("ClientId");
        if (!empty($request->getHeader("CurrentClientId"))) {
            $current_client_id = $request->getHeader("CurrentClientId");
        }

	 	$generic_model = $this->setClient($current_client_id, $generic_model);
        
        try {

            $generic_model->setDatabase($args['database']);

        } catch (\Exception $e) { // TODO: specialize this

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write( json_encode(['results' => []]) );

        }

        // JSON | ["results": \Ds\Vector] OR ["results": \Ds\Vector, "pages": \Ds\Vector] (TODO)
        $records_found = $generic_model->searchRecord(  $post_data, $logic );
		
		$response->getBody()->write($records_found);

        return $response;
	}

	/**
	 * Search Records Post
	 * 
	 * @param array $args
	 */
	public function searchRecordsPost(ServerRequestInterface $request, ResponseInterface $response, array $args){
		$logic = [];

	 	$generic_model = new Generic(
	 		// \Models\Interfaces\FileSystemInterface 
            new \Models\FileSystem\FileSystemBasic,
            // \Models\Interfaces\GitInterface
            new \Models\Git\GitBasic,
            // \Models\Interfaces\BagInterface
            new \Models\Bag\BagBasic
	 	);

	 	// this part is to be improved, right now the simple 
	 	// presence will change all comparisons to OR
		$post_data = $request->getParsedBody();
		if( isset($post_data['logic']) ){
		 	$logic = $post_data['logic'];
		 	unset($post_data['logic']);
		}

        $current_client_id = $request->getHeader("ClientId");
        if (!empty($request->getHeader("CurrentClientId"))) {
            $current_client_id = $request->getHeader("CurrentClientId");
        }

        $generic_model = $this->setClient($current_client_id, $generic_model);

		try {

            $generic_model->setDatabase($args['database']);

        } catch (\Exception $e) { // TODO: specialize this

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write( json_encode(['results' => []]) );

        }

		// JSON | ["results": \Ds\Vector] OR ["results": \Ds\Vector, "pages": \Ds\Vector] (TODO)
        $records_found = $generic_model->searchRecord($post_data, $logic);

        $response->getBody()->write( $records_found );

        return $response;
	}

	/**
	 * Persist record
	 * 
	 * Expected Request Body Format: 
	 * 	{
	 * 		"title": {string},
	 * 		"author": {string},
	 * 		"email": {string},
	 * 		"content": {string}
	 * 	}
	 * 
	 * @return JSON String - e.g: {"success": 1, "successMessage": {id}}
	 */
	public function saveGeneric(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$generic_model = new Generic(
	 		// \Models\Interfaces\FileSystemInterface 
            new \Models\FileSystem\FileSystemBasic,
            // \Models\Interfaces\GitInterface
            new \Models\Git\GitBasic,
            // \Models\Interfaces\BagInterface
            new \Models\Bag\BagBasic
	 	);

	 	$current_client_id = $request->getHeader("ClientId");
        if (!empty($request->getHeader("CurrentClientId"))) {
            $current_client_id = $request->getHeader("CurrentClientId");
        }

        $generic_model = $this->setClient($current_client_id, $generic_model);

        $generic_model->no_cache = false;

        try {

            $generic_model->setDatabase($args['database']);

        } catch (NotExistentDatabaseException $e) {

            $generic_model->createDatabase($args['database']);

        } catch (\Exception $e) { // TODO: specialize this

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write( json_encode([]) );

        }

        $result = $this->saveRecord($request, $response, $args, $generic_model);
        
	 	// place record address in the result

	 	return $response->withStatus(200)
                 ->withHeader('Content-Type', 'application/json')
                 ->write( $result );

	}

	/**
	 * This method specify the client Id from a Header parameter.
	 * 
	 * This header is validated in the OAuth2 lib.
	 * 
	 * @param mix $client_id
	 * @return Generic $generic_model
	 */
	private function setClient( $client_id, Generic $generic_model ){
		
		if( !empty($client_id) ){

	 		$client_id = $client_id;

	 		if( is_array($client_id) )
	 			$client_id = $client_id[0];

			$generic_model->setClientId( $client_id );
			
		}

		return $generic_model;

	}

	/**
	 * Deleted record
	 */
	public function deleteGeneric(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$generic_model = new Generic(
	 		// \Models\Interfaces\FileSystemInterface 
            new \Models\FileSystem\FileSystemBasic,
            // \Models\Interfaces\GitInterface
            new \Models\Git\GitBasic,
            // \Models\Interfaces\BagInterface
            new \Models\Bag\BagBasic
	 	);

		$current_client_id = $request->getHeader("ClientId");
        if (!empty($request->getHeader("CurrentClientId"))) {
            $current_client_id = $request->getHeader("CurrentClientId");
        }

        $generic_model = $this->setClient($current_client_id, $generic_model);

        try {

            $generic_model->setDatabase($args['database']);

        } catch (\Exception $e) { // TODO: specialize this

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write( json_encode([]) );

        }

	 	try {

	 		$result = $generic_model->delete($args['id']);

	 	} catch (\Exception $e) {
	 		
	 		$return_message = [
	 			"error" => 1,
	 			"message" => $e->getMessage()
	 		];

	 		return $response->withStatus(500)
                     ->withHeader('Content-Type', 'application/json')
                     ->write( json_encode( $return_message ) );

	 	}

	 	$return_message = [
 			"success" => 1,
 			"message" => "Record successfully removed!"
 		];

 		return $response->withStatus(200)
                 ->withHeader('Content-Type', 'application/json')
                 ->write( json_encode( $return_message ) );

	}

	/**
	 * This method is used to create a version after each change.
     * 
     * Description: It is necessary because the "git add" and 
     *              "git commit" are expensive once the database
     *              grows bigger.
	 */
	public function gitAsync(ServerRequestInterface $request, ResponseInterface $response, array $args){
        $request_body = $request->getParsedBody();

        $date1 = new \DateTime;

        $adapter = new Local( __DIR__."/../" );
        $filesystem = new Filesystem($adapter);

        $generic_model = new Generic(
            // \Models\Interfaces\FileSystemInterface 
            new \Models\FileSystem\FileSystemBasic,
            // \Models\Interfaces\GitInterface
            new \Models\Git\GitBasic,
            // \Models\Interfaces\BagInterface
            new \Models\Bag\BagBasic
        );

        $current_client_id = $request->getHeader("ClientId");
        if (!empty($request->getHeader("CurrentClientId"))) {
            $current_client_id = $request->getHeader("CurrentClientId");
        }

        $generic_model = $this->setClient($current_client_id, $generic_model);

        try {

            $generic_model->setDatabase($args['database']);

        } catch (\Exception $e) { // TODO: specialize this

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write( json_encode([]) );

        }

        $result = $generic_model->stageAndCommitAll();

        $date2 = new \DateTime;
        $date_diff = $date1->diff($date2);

        $filesystem->put("git_async_result", $result . ' - ' . date("Y-m-d H:i:s") . ' | ' . $date_diff->s . ' seconds.');
	}

    /**
     * This method is to update a cache of a specific database
     * 
     * @internal It comes from @save on the Generic Model
     */
    public function updateCacheAsync(ServerRequestInterface $request, ResponseInterface $response, array $args){
        $request_body = $request->getParsedBody();

        $date1 = new \DateTime;

        $adapter = new Local( __DIR__."/../" );
        $filesystem = new Filesystem($adapter);
        
        $generic_model = new Generic(
            // \Models\Interfaces\FileSystemInterface 
            new \Models\FileSystem\FileSystemBasic,
            // \Models\Interfaces\GitInterface
            new \Models\Git\GitBasic,
            // \Models\Interfaces\BagInterface
            new \Models\Bag\BagBasic
        );

        $current_client_id = $request->getHeader("ClientId");
        if (!empty($request->getHeader("CurrentClientId"))) {
            $current_client_id = $request->getHeader("CurrentClientId");
        }

        $generic_model = $this->setClient($current_client_id, $generic_model);

        try {

            $generic_model->setDatabase($request_body['database']);

        } catch (\Exception $e) { // TODO: specialize this

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write( json_encode([]) );

        }

        $cache_helper = new \Helpers\CacheHelper;
        $result = $generic_model->getGitData( $cache_helper );

        $date2 = new \DateTime;
        $date_diff = $date1->diff($date2);

        $filesystem->put("updatecache_async_result", $result . ' - ' . date("Y-m-d H:i:s") . ' | ' . $date_diff->s . ' seconds.');
    }

}
