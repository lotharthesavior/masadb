<?php 

namespace Models\Interfaces;

interface GenericInterface
{
	/**
	 * Set the database
	 * 
	 * @param String $database
	 * @return void
	 */
	public function setDatabase( $database );

	/**
	 * Get the database
	 * 
	 * @return string
	 */
	public function getDatabase();

	/**
	 * Set the Client ID
	 * 
	 * @param String $client_id
	 * @return void
	 */
	public function setClientId( $client_id );

	/**
	 * Get the Client ID
	 * 
	 * @return string
	 */
	public function getClientId();

}