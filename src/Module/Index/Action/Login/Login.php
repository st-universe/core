<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Login;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;

final class Login implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_LOGIN';

    private LoginRequestInterface $loginRequest;

    private SessionInterface $session;

    public function __construct(
        LoginRequestInterface $loginRequest,
        SessionInterface $session
    ) {
        $this->loginRequest = $loginRequest;
        $this->session = $session;
    }

    public function handle(GameControllerInterface $game): void
    {
        try {
            $this->session->login(
                $this->loginRequest->getLoginName(),
                $this->loginRequest->getPassword()
            );
        } catch (LoginException $e) {
            $game->setLoginError($e->getMessage());
        }
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
