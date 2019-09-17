<?php
declare(strict_types=1);

namespace Stu\Module\Api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Opis\JsonSchema\{
    Validator, ValidationResult, Schema
};

abstract class Action
{
    protected const SCHEMA_FILE = '';

    protected $logger;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var JsonResponseInterface
     */
    protected $response;

    /**
     * @var array
     */
    protected $args;

//    public function __construct(LoggerInterface $logger)
//    {
//        $this->logger = $logger;
//    }

    public function __invoke(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): ResponseInterface {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->action($request, $response, $args);
        } catch (\Exception $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
    }

    /**
     * @throws HttpBadRequestException
     */
    abstract protected function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface;

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

}
