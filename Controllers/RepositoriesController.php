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
	 * 
	 */
	public function list(ServerRequestInterface $request, ResponseInterface $response){

		$notes_model = new Notes();

		$result = $notes_model->findAll();

		return $result;

	}

	/**
	 * 
	 */
	public function getRepositories(ServerRequestInterface $request, ResponseInterface $response){

		$repositories_model = new Repositories();

		$result = $repositories_model->findAll();

		$response->getBody()->write( json_encode($result) );

    	return $response;

	}

}