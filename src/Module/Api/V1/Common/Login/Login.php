<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\Login;

use Firebase\JWT\JWT;
use Noodlehaus\ConfigInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stu\Component\ErrorHandling\ErrorCodeEnum;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Request\JsonSchemaRequestInterface;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;

final class Login extends Action
{
    public const JSON_SCHEMA_FILE = __DIR__ . '/login.json';

    private SessionInterface $session;

    private JsonSchemaRequestInterface $jsonSchemaRequest;

    private ConfigInterface $config;

    public function __construct(
        SessionInterface $session,
        JsonSchemaRequestInterface $jsonSchemaRequest,
        ConfigInterface $config
    ) {
        $this->session = $session;
        $this->config = $config;
        $this->jsonSchemaRequest = $jsonSchemaRequest;
    }

    public function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        $data = $this->jsonSchemaRequest->getData($this);

        try {
            $this->session->login($data->username, $data->password);

            $token = JWT::encode(
                [
                    'iat' => time(),
                    'exp' => time() + $this->config->get('api.jwt_validity_period'),
                    'stu' => [
                        'uid' => $this->session->getUser()->getId()
                    ]
                ],
                $this->config->get('api.jwt_secret')
            );
        } catch (LoginException $e) {
            return $response->withError(
                ErrorCodeEnum::AUTHENTICATION_FAILED,
                $e->getMessage()
            );
        }

        return $response->withData([
            'token' => $token,
        ]);
    }
}
