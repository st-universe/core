<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\Login;

use Psr\Http\Message\ResponseInterface;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\ActionError;

final class Login extends Action
{
    private $session;

    public function __construct(
        SessionInterface $session
    ) {
        $this->session = $session;
    }

    public function action(): ResponseInterface
    {
        $data = $this->getFormData();

        try {
            $this->session->login($data['username'], $data['password']);
        } catch (LoginException $e) {
            return $this->respondWithError(
                new ActionError(
                    ActionError::VERIFICATION_ERROR
                )
            );
        }

        return $this->respondWithData([
            'userId' => $this->session->getUser()->getId()
        ]);
    }
}