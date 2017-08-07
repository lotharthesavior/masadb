<?php

namespace Repositories;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Repositories\UserRepositoryInterface;

use \Models\OAuth2\User;

class UserRepository implements UserRepositoryInterface
{

	/**
	 * 
	 */
	public function __construct(){

	}

	/**
     * Get a user entity.
     *
     * @param string                $username
     * @param string                $password
     * @param string                $grantType    The grant type used
     * @param ClientEntityInterface $clientEntity
     * 
     * @todo finish this
     *
     * @return UserEntityInterface
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ){

        $user_model = new User;

        return $user_model;

    }

}