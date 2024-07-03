<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Login;

use Override;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class Login implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_LOGIN';

    public function __construct(private LoginRequestInterface $loginRequest, private SessionInterface $session)
    {
    }

    /**
     * @throws LoginException
     */
    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $success = $this->session->login(
            $this->loginRequest->getLoginName(),
            $this->loginRequest->getPassword()
        );

        if ($success) {
            $view = $game->getUser()->getDefaultView();
            $game->redirectTo(sprintf('/%s', $view->getPhpPage()));
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
