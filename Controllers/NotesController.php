<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Notes;

class NotesController extends MasaController
{
	
	use \Controllers\traits\commonController;

	/**
	 * 
	 */
	public function __construct(){}

	/**
	 * 
	 */
	public function getNotes(ServerRequestInterface $request, ResponseInterface $response){

		$this->oauthBefore();

	 	$model = new Notes();

		$result = $model->findAll();

		$response->getBody()->write( json_encode($result) );

    	return $response;

	 }

	/**
	 * 
	 */
	public function getNote(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$notes_model = new Notes();

		$note = $notes_model->find( $args['id'] );

		$response->getBody()->write( json_encode($note) );

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
	 public function saveNote(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$notes_model = new Notes();

	 	return $this->saveRecord($request, $response, $args, $notes_model);

	 }

}