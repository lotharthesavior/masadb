<?php

namespace Repositories;

use \Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

use Models\OAuth2\Clients;

use \Models\FileSystem\FileSystemBasic;
use \Models\Git\GitBasic;
use \Models\Bag\BagBasic;

class ClientRepository implements ClientRepositoryInterface
{

	/**
     * Get a client.
     *
     * @param string      $clientIdentifier   The client's identifier
     * @param string      $grantType          The grant type used
     * @param null|string $clientSecret       The client's secret (if sent)
     * @param bool        $mustValidateSecret If true the client must attempt to validate the secret if the client
     *                                        is confidential
     *
     * @return ClientEntityInterface
     */
    public function getClientEntity(
        $clientIdentifier, 
        $grantType, 
        $clientSecret = null, 
        $mustValidateSecret = true
    ){
        
        $client_model = new Clients(
            // \Models\Interfaces\FileSystemInterface 
            new FileSystemBasic,
            // \Models\Interfaces\GitInterface
            new GitBasic,
            // \Models\Interfaces\BagInterface
            new BagBasic
        );

        $client_model->find($clientIdentifier);

        if( $client_model->file_content->secret_key != $clientSecret ){

            throw new Exception("Key not valid!");

        }

        return $client_model;

    }

}