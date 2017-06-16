<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use \Models\Tickets;

class TicketsController extends MasaController
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
	public function getTickets(ServerRequestInterface $request, ResponseInterface $response){

		$this->oauthBefore();

	 	$model = new Tickets();

	 	$model->sortType = "creation_DESC";

		$result = $model->findAll();

		$response->getBody()->write( json_encode($result) );

    		return $response;

	 }

	/**
	 * 
	 */
	public function getTicket(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$tickets_model = new Tickets();

		$ticket = $tickets_model->find( $args['id'] );

		$response->getBody()->write( json_encode($ticket) );

		return $response;

	}

	/**
	 * Search ticket
	 * 
	 * @param Array $args | ['field' => string, 'value' => string]
	 */
	public function searchTicket(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$tickets_model = new Tickets();

	 	$args = $this->processUnlimitedParams( $args );

		$ticket = $tickets_model->search( $args['field'], $args['value'] );
		
		$ticket = array_values($ticket);

		$response->getBody()->write( json_encode($ticket) );

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
	public function saveTicket(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$tickets_model = new Tickets();

	 	$result = $this->saveRecord($request, $response, $args, $tickets_model);

		return $result;

	}

	/**
	 * 
	 */
	public function deleteTicket(ServerRequestInterface $request, ResponseInterface $response, array $args){

	 	$tickets_model = new Tickets();

	 	$result = $tickets_model->delete($args['id']);

		return $result;

	}

}
