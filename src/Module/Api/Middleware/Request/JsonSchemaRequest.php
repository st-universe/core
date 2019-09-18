<?php

namespace Stu\Module\Api\Middleware\Request;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use stdClass;
use Stu\Module\Api\Middleware\Action;

final class JsonSchemaRequest implements JsonSchemaRequestInterface
{
    /**
     * @var null|ServerRequestInterface
     */
    private $request;

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function getData(Action $action): stdClass
    {
        if ($this->request === null) {
           throw new HttpInternalServerErrorException(null, 'Request not set');
        }
        
        $jsonSchemaFile = $action->getJsonSchemaFile();
        
        if ($jsonSchemaFile === null) {
            throw new HttpInternalServerErrorException($this->request, 'No schema found');
        }
        
        $input = json_decode(file_get_contents('php://input'));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpBadRequestException($this->request, 'Malformed JSON input.');
        }

        $schema = Schema::fromJsonString(file_get_contents($action->getJsonSchemaFile()));

        $validator = new Validator();

        /** @var ValidationResult $result */
        $result = $validator->schemaValidation($input, $schema);

        if (!$result->isValid()) {
            $error = $result->getFirstError();

            throw new HttpBadRequestException(
                $this->request,
                sprintf('%s - %s', $error->keyword(), json_encode($error->keywordArgs()))
            );
        }

        return $input;
    }
}