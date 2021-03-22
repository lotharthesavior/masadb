<?php

namespace Controllers;

use Exception;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Models\Interfaces\FileSystemInterface;
use Models\Interfaces\GitInterface;
use Models\Interfaces\BagInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Plugin\ListPaths;
use League\Flysystem\Plugin\ListWith;
use League\Flysystem\Plugin\GetWithMetadata;

use Models\Exceptions\NotExistentDatabaseException;
use Models\FileSystem\FileSystemBasic;
use Models\Git\GitBasic;
use Models\Bag\BagBasic;
use Models\Generic;

use Controllers\traits\CommonController;
use Controllers\traits\LogController;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class MasaDBController extends Abstraction\MasaController
{
    use CommonController,
        LogController;

    /** @var ContainerInterface */
    protected $container;

    /** @var Logger */
    protected $baseLog;

    /**
     * Start the controller instantiating the Slim Container
     *
     * @todo move this to a controller parent class
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->baseLog = new Logger('base');
        $this->baseLog->pushHandler(new StreamHandler('./logs/base-log.log', Logger::INFO));
    }

    /**
     * Fetch All Records
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function getFullCollection(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        return $this->searchRecords($request, $response, $args);
    }

    /**
     * Get a Single Record
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function getGeneric(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $args['key'] = 'id';
        $args['value'] = $args['id'];

        unset($args['id']);

        return $this->searchRecords($request, $response, $args);
    }

    /**
     * Get a Single Record
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function getGenericFile(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $args['key'] = 'id';
        $args['value'] = $queryParams['address'];

        return $this->searchRecords($request, $response, $args);
    }

    /**
     * Search Records
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args | ['field' => string, 'value' => string]
     *
     * @return ResponseInterface
     */
    public function searchRecords(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $logic = [];

        $generic_model = $this->container->get(Generic::class);

        // This part is to be improved, right now the simple
        // presence will change all comparisons to OR.
        $post_data = [];
        if (isset($args['key']) && isset($args['value'])) {
            $post_data[$args['key']] = $args['value'];
        }

        if (isset($post_data['logic'])) {
            $logic = $post_data['logic'];
            unset($post_data['logic']);
        }

        $current_client_id = $request->getHeader("ClientId");
        if (!empty($request->getHeader("CurrentClientId"))) {
            $current_client_id = $request->getHeader("CurrentClientId");
        }

        $generic_model = $this->setClient($current_client_id, $generic_model);

        try {

            $generic_model->setDatabase($args['database']);

        } catch (Exception $e) { // TODO: specialize this

            $this->baseLog->error("Error: " . $this->prepareLogInfoWithException(
                __FILE__,
                __CLASS__,
                __METHOD__,
                __LINE__,
                $e
            ));
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode([
                    "success" => 0,
                    "error" => "There was an error while searching for records.",
                ]));

        }

        /** @var string $records_found ["results": \Ds\Vector] OR ["results": \Ds\Vector, "pages": \Ds\Vector] */
        $records_found = $generic_model->searchRecord($post_data, $logic);

        $response->getBody()->write($records_found);

        $response = $response->withHeader('Content-Type', 'application/json');

        return $response->withStatus(200);
    }

    /**
     * Search Records Post
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function searchRecordsPost(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $logic = [];

        $generic_model = $this->container->get(Generic::class);

        // This part is to be improved, right now the simple
        // presence will change all comparisons to OR.
        $post_data = ($request->getParsedBody() === null) ? [] : $request->getParsedBody();
        if (isset($post_data['logic'])) {
            $logic = $post_data['logic'];
            unset($post_data['logic']);
        }

        $current_client_id = $request->getHeader("ClientId");
        if (!empty($request->getHeader("CurrentClientId"))) {
            $current_client_id = $request->getHeader("CurrentClientId");
        }

        $generic_model = $this->setClient($current_client_id, $generic_model);

        try {

            $generic_model->setDatabase($args['database']);

        } catch (Exception $e) { // TODO: specialize this.

            $this->baseLog->error("Error: " . $this->prepareLogInfoWithException(
                __FILE__,
                __CLASS__,
                __METHOD__,
                __LINE__,
                $e
            ));
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode([
                    "success" => 0,
                    "error" => "There was an error while searching for records.",
                ]));

        }

        /** @var string $records_found ["results": \Ds\Vector] OR ["results": \Ds\Vector, "pages": \Ds\Vector] */
        $records_found = $generic_model->searchRecord($post_data, $logic);

        $response->getBody()->write($records_found);

        $response = $response->withHeader('Content-Type', 'application/json');

        return $response->withStatus(200);
    }

    /**
     * Persist record
     *
     * Expected Request Body Format:
     *    {
     *        "title": {string},
     *        "author": {string},
     *        "email": {string},
     *        "content": {string}
     *    }
     *
     * @todo There is some space to responde according to the "Accept" header.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     *
     * @return ResponseInterface {"success": 1, "successMessage": {id}}
     */
    public function saveGeneric(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $generic_model = $this->container->get(Generic::class);

        $current_client_id = $request->getHeader("ClientId");
        if (!empty($request->getHeader("CurrentClientId"))) {
            $current_client_id = $request->getHeader("CurrentClientId");
        }

        $generic_model = $this->setClient($current_client_id, $generic_model);

        $generic_model->setNoCache(false);

        try {

            $generic_model->setDatabase($args['database']);

        } catch (NotExistentDatabaseException $e) {

            $this->baseLog->error("Error: " . $this->prepareLogInfoWithException(
                __FILE__,
                __CLASS__,
                __METHOD__,
                __LINE__,
                $e
            ));
            $generic_model->createDatabase($args['database']);

        } catch (Exception $e) { // TODO: specialize this

            $this->baseLog->error("Error: " . $this->prepareLogInfoWithException(
                __FILE__,
                __CLASS__,
                __METHOD__,
                __LINE__,
                $e
            ));
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode([
                    "success" => 0,
                    "error" => "There was an error while saving record.",
                ]));

        }

        $result = $this->saveRecord($request, $response, $args, $generic_model);

        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write($result);
    }

    /**
     * Deleted record
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function deleteGeneric(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $generic_model = $this->container->get(Generic::class);

        $current_client_id = $request->getHeader("ClientId");
        if (!empty($request->getHeader("CurrentClientId"))) {
            $current_client_id = $request->getHeader("CurrentClientId");
        }

        $generic_model = $this->setClient($current_client_id, $generic_model);

        try {

            $generic_model->setDatabase($args['database']);

        } catch (Exception $e) { // TODO: specialize this

            $this->baseLog->error("Error: " . $this->prepareLogInfoWithException(
                __FILE__,
                __CLASS__,
                __METHOD__,
                __LINE__,
                $e
            ));
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode([
                    "success" => 0,
                    "error" => "There was an error while deleting record.",
                ]));

        }

        try {

            $result = $generic_model->delete($args['id']);

        } catch (Exception $e) {

            $this->baseLog->error("Error: " . $this->prepareLogInfoWithException(
                __FILE__,
                __CLASS__,
                __METHOD__,
                __LINE__,
                $e
            ));

            $return_message = [
                "error" => 1,
                "message" => $e->getMessage()
            ];

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($return_message));

        }

        $return_message = [
            "success" => 1,
            "message" => "Record successfully removed!"
        ];

        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($return_message));
    }

    /**
     * Deleted record
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function deleteGenericFile(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        /** @var array $queryParams */
        $queryParams = $request->getQueryParams();

        $generic_model = $this->container->get(Generic::class);

        $current_client_id = $request->getHeader("ClientId");
        if (!empty($request->getHeader("CurrentClientId"))) {
            $current_client_id = $request->getHeader("CurrentClientId");
        }

        $generic_model = $this->setClient($current_client_id, $generic_model);

        try {

            $generic_model->setDatabase($args['database']);

        } catch (Exception $e) { // TODO: specialize this

            $this->baseLog->error("Error: " . $this->prepareLogInfoWithException(
                __FILE__,
                __CLASS__,
                __METHOD__,
                __LINE__,
                $e
            ));
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode([
                    "success" => 0,
                    "error" => "There was an error while deleting record.",
                ]));

        }

        try {

            $args['id'] = $queryParams['address'];
            $generic_model->delete($args['id']);

        } catch (Exception $e) {

            $this->baseLog->error("Error: " . $this->prepareLogInfoWithException(
                __FILE__,
                __CLASS__,
                __METHOD__,
                __LINE__,
                $e
            ));

            $return_message = [
                "error" => 1,
                "message" => $e->getMessage()
            ];

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($return_message));

        }

        $return_message = [
            "success" => 1,
            "message" => "Record successfully removed!"
        ];

        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($return_message));
    }

    /**
     * This method specify the client Id from a Header parameter.
     *
     * This header is validated in the OAuth2 lib.
     *
     * @param mixed $client_id
     * @param Generic $generic_model
     *
     * @return Generic
     */
    private function setClient($client_id, Generic $generic_model): Generic
    {
        if (!empty($client_id)) {

            $generic_model->setClientId(is_array($client_id) ? $client_id[0] : $client_id);

        }

        return $generic_model;

    }
}
