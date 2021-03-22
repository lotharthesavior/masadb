<?php

namespace Repositories;

use Exception;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Models\OAuth2\Clients;
use Repositories\Abstraction\AbstractRepository;

class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
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
        /** @var Clients $access_token_model */
        $client_model = $this->container->get(Clients::class);

        $client_model->find($clientIdentifier);

        if ($client_model->file_content->secret_key != $clientSecret) {
            throw new Exception("Key not valid!");
        }

        return $client_model;
    }

}
