<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Repositories;

class RepositoriesController
{

	/**
	 * 
	 */
	public function __construct(){}

	/**
	 * Get All Repositories
	 */
	public function getRepositories(ServerRequestInterface $request, ResponseInterface $response){

		$repositories_model = new Repositories();

		$result = $repositories_model->findAll();

		$response->getBody()->write( json_encode($result) );

    	return $response;

	}

	/**
	 * Get Repository
	 * 
	 * Necessary Data from the Repository:
	 * 
	 * 1. Address
	 * 2. ls-tree recursive
	 * 3. list of branches
	 * 4. readme file content
	 * 
	 * Ex.: http://lotharthesavior.dns1.us/resources/repositories/savioresende/
	 * 
	 */
	public function getRepository(ServerRequestInterface $request, ResponseInterface $response){

		

	}

}