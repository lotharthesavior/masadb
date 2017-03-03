<?php

namespace Controllers\traits;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait commonController
{

	/**
	 * Expected Body Format: 
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
	 * @param \Models\GitModel $model
	 */
	public function saveRecord(ServerRequestInterface $request, ResponseInterface $response, array $args, \Models\GitModel $model){

	 	// request data
		
		$request_body = json_decode($request->getBody(), true);

		$id = null;
		if( isset($args['id']) ){
			$id = $args['id'];
		}

		// model interation

		try {

			$client_data = array_merge(["id" => $id, "content" => $request_body]);

			$message = $model->save( $client_data );
			
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

	/**
	 * 
	 */
	protected function deleteRecord(ServerRequestInterface $request, ResponseInterface $response, array $args, \Models\GitModel $model){

		try {

			$message = $model->delete( $args['id'] );
			
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