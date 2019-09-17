<?php

declare(strict_types=1);

namespace Stu\Module\Api\Middleware;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

final class Responder
{


    /**
     * @return array|object
     * @throws HttpBadRequestException
     */
    protected function getFormData()
    {
        $input = json_decode(file_get_contents('php://input'));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpBadRequestException($this->request, 'Malformed JSON input.');
        }

        $schema = Schema::fromJsonString(file_get_contents(static::SCHEMA_FILE));

        $validator = new Validator();

        /** @var ValidationResult $result */
        $result = $validator->schemaValidation($input, $schema);

        if (!$result->isValid()) {
            $error = $result->getFirstError();

            throw new HttpBadRequestException(
                $this->request,
                sprintf(
                    '%s - %s',
                    $error->keyword(),
                    json_encode($error->keywordArgs())
                )
            );
        }

        return $input;
    }

    /**
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    protected function respondWithError(ActionError $error): ResponseInterface
    {
        $payload = new ActionPayload(200, null, $error);
        return $this->respond($payload);
    }

    protected function respondWithData($data = null): ResponseInterface
    {
        $payload = new ActionPayload(200, $data);
        return $this->respond($payload);
    }

    protected function respond(ActionPayload $payload): ResponseInterface
    {
        // @todo use custom emitter and enable cors

        $json = json_encode($payload);
        $this->response->getBody()->write($json);

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader(
                'Access-Control-Allow-Headers',
                'X-Requested-With, Content-Type, Accept, Origin, Authorization'
            )
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withAddedHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache');
    }
}