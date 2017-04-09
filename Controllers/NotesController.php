<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Notes;

class NotesController extends MasaController
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
	 * Search Note
	 * 
	 * @param Array $args | ['field' => string, 'value' => string]
	 */
	public function searchNote(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$notes_model = new Notes();

		$note = $notes_model->search( $args['field'], $args['value'] );

		var_dump($note);exit;

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

	 	$result = $this->saveRecord($request, $response, $args, $notes_model);

		return $result;

	}

	/**
	 * 
	 */
	public function deleteNote(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$notes_model = new Notes();

	 	$result = $notes_model->delete($args['id']);

		return $result;

	}

}
