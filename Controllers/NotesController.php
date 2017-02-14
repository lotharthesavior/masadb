<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Notes;

class NotesController
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
	public function getNotes(ServerRequestInterface $request, ResponseInterface $response){

		$notes_model = new Notes();

		$result = $notes_model->findAll();

		$response->getBody()->write( json_encode($result) );

    	return $response;

	}

}