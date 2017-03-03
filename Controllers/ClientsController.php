<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\OAuth2\Clients;

class ClientsController
{
	
	use \Controllers\traits\commonController;

	/**
	 * 
	 */
	public function __construct(){}

	/**
	 * 
	 */
	public function getClients(ServerRequestInterface $request, ResponseInterface $response){

	 	$model = new Clients();

		$result = $model->findAll();

		$response->getBody()->write( json_encode($result) );

    	return $response;

	 }

	/**
	 * 
	 */
	public function getClient(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$users_model = new Clients;

	 	try {

			$result = $users_model->find( $args['id'] );

		} catch (\Exception $e) {
			
			$result = [
				"Error"        => 1, 
				"ErrorMessage" => $e->getMessage()
			];

		}

		$response->getBody()->write( json_encode($result) );

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
	 public function saveClient(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$users_model = new Clients();

	 	return $this->saveRecord($request, $response, $args, $users_model);

	 }

	 /**
	  * 
	  */
	 public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$model = new Clients;

	 	return $this->deleteRecord($request, $response, $args, $model);

	 }

}