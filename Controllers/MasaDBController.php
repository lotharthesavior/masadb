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

                // $generic_model->setDatabase("users");

		$record = $generic_model->find( $args['id'] );

		$response->getBody()->write( json_encode($record) );

    	return $response;

	}

	/**
	 * Search Note
	 * 
	 * @param Array $args | ['field' => string, 'value' => string]
	 */
	public function searchRecords(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$db_model = new Generic();

        $db_model->setDatabase( $args['database'] );

	 	// $args = $this->processUnlimitedParams( $args );

        $record = $db_model->search( $args['key'], $args['value'] );
        echo "<pre>";var_dump($record);exit;	

		$record = array_values($record);

		$response->getBody()->write( json_encode($record) );

                return $response;

	}

	/**
	 * Expected Body Format: 
	 * 	{
	 * 		"title": {string},
	 * 		"author": {string},
	 * 		"email": {string},
	 * 		"content": {string}
	 * 	}
	 */
	public function saveGeneric(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$generic_model = new Generic();

                $generic_model->setDatabase( $args['database'] );
// var_dump($request->getParsedBody());exit;
	 	$result = $this->saveRecord($request, $response, $args, $generic_model);

		return $result;

	}

	/**
	 * Deleted record
	 */
	public function deleteGeneric(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$generic_model = new Generic();

	 	$result = $generic_model->delete($args['id']);

		return $result;

	}

}
