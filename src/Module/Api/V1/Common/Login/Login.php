<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\Login;

use Firebase\JWT\JWT;
use Noodlehaus\ConfigInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\ActionError;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;

final class Login extends Action
{
    protected const SCHEMA_FILE = __DIR__ . '/login.json';

    private $session;

    private $config;

    public function __construct(
        SessionInterface $session,
        ConfigInterface $config
    ) {
        $this->session = $session;
        $this->config = $config;
    }

    public function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        $data = $this->getFormData();

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
                ActionError::VERIFICATION_ERROR,
                $e->getMessage()
            );
        }

        return $response->withData([
            'token' => $token,
        ]);
    }
}