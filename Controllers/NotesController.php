<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Notes;

class NotesController extends MasaController
{

	/**
	 * 
	 */
	public function __construct(){}

	/**
	 * 
	 */
	public function getNotes(ServerRequestInterface $request, ResponseInterface $response){

	 	$model = new Notes();

		$result = $model->findAll();

		$response->getBody()->write( json_encode($result) );

    	return $response;

	 }

	/**
	 * 
	 */
	public function getNote(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$repositories_model = new Notes();

		$repository = $repositories_model->find( $args['id'] );

		return $repository;

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
	 public function saveNote(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	// request data
		
		$request_body = json_decode($request->getBody(), true);

		$id = null;
		if( isset($args['id']) ){
			$id = $args['id'];
		}

		// model interation

		$repositories_model = new Notes();

		try {

			$client_data = array_merge(["id" => $id, "content" => $request_body]);

			$message = $repositories_model->save( $client_data );
			
			$result = [
				"Success"        => 1,
				"SuccessMessage" => $message
			];

		} catch (\Exception $e) {
			
			$result = [
				"Error"        => 1, 
				"ErrorMessage" => $e->getMessage()
			];

		}

		$response->getBody()->write( json_encode($result) );

    	return $response;

	 }

}