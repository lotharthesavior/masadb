<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Users;

/**
 *
 * Controller for user management
 * 
 * @author Savio Resende <savio@savioresende.com.br> 
 * 
 */

class UsersController
{
	
	use \Controllers\traits\commonController;

	/**
	 * 
	 */
	public function __construct(){}

	/**
	 * Get a list of users
	 * 
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 */
	public function getUsers(ServerRequestInterface $request, ResponseInterface $response){

	 	$model = new Users();

		$result = $model->findAll();

		$response->getBody()->write( json_encode($result) );

    	return $response;

	 }

	/**
	 * Get user by id
	 * 
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param Array $args
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
	  * Persist user information
	  * 
	  * Expected Body Format: 
	  * 
	  * 	{
	  * 		"title": {string},
	  * 		"author": {string},
	  * 		"email": {string},
	  * 		"content": {string}
	  * 	}
	  * 
	  * @param ServerRequestInterface $request
	  * @param ResponseInterface $response
	  * @param Array $args
	  * @return Boolean
	  */
	 public function saveUser(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$users_model = new Users();

	 	return $this->saveRecord($request, $response, $args, $users_model);

	 }

	 /**
	  * Delete user record
	  * 
	  * @param ServerRequestInterface $request
	  * @param ResponseInterface $response
	  * @param Array $args
	  * @return Boolean
	  */
	 public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$model = new Users;

	 	return $this->deleteRecord($request, $response, $args, $model);

	 }

}