<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Models\Generic;

class MasaDBController extends MasaController
{
	
	use \Controllers\traits\commonController;

	protected $container;

    /**
     * Start the controller instantiating the Slim Container
     * @todo move this to a controller parent class
     */
    public function __construct($container){
        $this->container = $container;
    }

	/**
	 * Fetch all records
	 */
	public function getFullCollection(ServerRequestInterface $request, ResponseInterface $response, $args){

		$this->oauthBefore();

		$generic_model = new Generic();

	 	if( !empty($request->getHeader("ClientId")) ){
			$generic_model->setClientId( $request->getHeader("ClientId") );
		}

	 	$generic_model->setDatabase($args["database"]);

	 	$generic_model->sortType = "creation_DESC";

		$result = $generic_model->findAll();

		$response->getBody()->write( json_encode($result) );

    	return $response;

	}

	/**
	 * Get single record
	 */
	public function getGeneric(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$generic_model = new Generic();

	 	if( !empty($request->getHeader("ClientId")) ){
			$generic_model->setClientId( $request->getHeader("ClientId") );
		}

        $generic_model->setDatabase( $args['database'] );

        try {
        	
			$record = $generic_model->find( $args['id'] );

        } catch (\Exception $e) {

        	$return_message = [
	 			"status" => "error",
	 			"message" => $e->getMessage()
	 		];

	 		return $response->withStatus(200)
                     ->withHeader('Content-Type', 'application/json')
                     ->write( json_encode( $return_message ) );
        	
        }

		$response->getBody()->write( $record );

    	return $response;

	}

	/**
	 * Search Note
	 * 
	 * @param Array $args | ['field' => string, 'value' => string]
	 */
	public function searchRecords(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$generic_model = new Generic();

	 	if( !empty($request->getHeader("ClientId")) ){
			$generic_model->setClientId( $request->getHeader("ClientId") );
		}

        $generic_model->setDatabase( $args['database'] );

        $record = $generic_model->search( $args['key'], $args['value'] );

		$record = array_values($record);

		$response->getBody()->write( json_encode($record) );

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

	 	$generic_model = new Generic();

	 	if( !empty($request->getHeader("ClientId")) ){
			$generic_model->setClientId( $request->getHeader("ClientId") );
		}

        $generic_model->setDatabase( $args['database'] );

	 	$result = $this->saveRecord($request, $response, $args, $generic_model);

	 	return $response->withStatus(200)
                 ->withHeader('Content-Type', 'application/json')
                 ->write( $result );

	}

	/**
	 * Deleted record
	 */
	public function deleteGeneric(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$generic_model = new Generic();

	 	if( !empty($request->getHeader("ClientId")) ){
			$generic_model->setClientId( $request->getHeader("ClientId") );
		}

	 	$generic_model->setDatabase( $args['database'] );

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

}
