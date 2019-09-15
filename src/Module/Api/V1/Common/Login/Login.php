<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\Login;

use Firebase\JWT\JWT;
use Noodlehaus\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\ActionError;

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

    public function action(): ResponseInterface
    {
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
            return $this->respondWithError(
                new ActionError(
                    ActionError::VERIFICATION_ERROR
                )
            );
        }

        return $this->respondWithData([
            'token' => $token,
        ]);
    }
}