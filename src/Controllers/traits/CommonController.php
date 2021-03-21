<?php

namespace Controllers\traits;

use Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

use Models\Abstraction\GitDAO;

trait CommonController
{

    /**
     * Expected Body Format:
     *    {
     *        "title": {string},
     *        "author": {string},
     *        "email": {string},
     *        "content": {string}
     *    }
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @param GitDAO $model
     *
     * @return string {"error": 1, "errorMessage": string}
     *                || {"success": 1, "successMessage": {inserted_id}}
     */
    public function saveRecord(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
        GitDAO &$model
    ): string
    {
        /** @var null|array|object $request_body */
        $request_body = $this->getBodyFromRequest($request);

        $id = null;
        if (isset($args['id'])) {
            $id = $args['id'];
        }

        // Model iteration.
        try {

            $client_data = [
                "id" => $id,
                "content" => $request_body,
            ];
            $message = $model->save($client_data);
            $result = [
                "success" => 1,
                "successMessage" => $message
            ];

        } catch (Exception $e) {

            $result = [
                "error" => 1,
                "errorMessage" => $e->getMessage()
            ];

        }

        return json_encode($result);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @param GitDAO $model
     *
     * @return ResponseInterface
     */
    protected function deleteRecord(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
        GitDAO $model
    ): ResponseInterface
    {

        try {

            $message = $model->delete($args['id']);

            $result = [
                "success" => 1,
                "successMessage" => $message
            ];

        } catch (Exception $e) {

            $result = [
                "error" => 1,
                "errorMessage" => $e->getMessage()
            ];

        }

        $response->getBody()->write(json_encode($result));

        return $response;

    }

    /**
     * Organinize unlimited params with the Slimframework Router
     *
     * @param string $params
     *
     * @return array
     */
    protected function processUnlimitedParams(string $params): array
    {
        $param = [];
        $values = [];

        $params = explode("/", $params['params']);


        foreach ($params as $key => $value) {
            if ($key % 2 == 0) {
                array_push($param, $value);
            } else {
                array_push($values, $value);
            }
        }

        return [
            'field' => $param,
            'value' => $values
        ];
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    private function getBodyFromRequest(ServerRequestInterface $request): array
    {
        $request_body = $request->getParsedBody();

        // Try once more.
        if (is_null($request_body)) {
            /* @var StreamInterface */
            $body = $request->getBody();
            $body->rewind();
            $request_body = json_decode($body->read($body->getSize()), true);
        }

        return (array) $request_body;
    }

}
