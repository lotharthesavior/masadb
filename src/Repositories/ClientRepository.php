<?php

namespace Repositories;

use Exception;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

use Models\Interfaces\FileSystemInterface;
use Models\Interfaces\GitInterface;
use Models\Interfaces\BagInterface;

use Models\OAuth2\Clients;
use Models\FileSystem\FileSystemBasic;
use Models\Git\GitBasic;
use Models\Bag\BagBasic;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * Get a client.
     *
     * @param string $clientIdentifier The client's identifier
     * @param string $grantType The grant type used
     * @param null|string $clientSecret The client's secret (if sent)
     * @param bool $mustValidateSecret If true the client must attempt to validate the secret if the client is confidential
     *
     * @return ClientEntityInterface
     *
     * @throws Exception
     */
    public function getClientEntity(
        $clientIdentifier,
        $grantType,
        $clientSecret = null,
        $mustValidateSecret = true
    ): ClientEntityInterface
    {
        $client_model = new Clients(
            new FileSystemBasic, // FileSystemInterface
            new GitBasic,        // GitInterface
            new BagBasic         // BagInterface
            []
        );

        $client_model->find($clientIdentifier);

        if ($client_model->file_content->secret_key != $clientSecret) {
            throw new Exception("Key not valid!");
        }

        return $client_model;
    }

}
