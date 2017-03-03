<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Users;

class UsersController
{
	
	use \Controllers\traits\commonController;

	/**
	 * 
	 */
	public function __construct(){}

	/**
	 * 
	 */
	public function getUsers(ServerRequestInterface $request, ResponseInterface $response){

	 	$model = new Users();

		$result = $model->findAll();

		$response->getBody()->write( json_encode($result) );

    	return $response;

	 }

	/**
	 * 
	 */
	public function getUser(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$users_model = new Users;

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
	 public function saveUser(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$users_model = new Users();

	 	return $this->saveRecord($request, $response, $args, $users_model);

	 }

	 /**
	  * 
	  */
	 public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$model = new Users;

	 	return $this->deleteRecord($request, $response, $args, $model);

	 }

}