<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Login;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

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

    /**
     * @throws LoginException
     */
    public function handle(GameControllerInterface $game): void
    {
        $success = $this->session->login(
            $this->loginRequest->getLoginName(),
            $this->loginRequest->getPassword()
        );

        $startpage = $game->getUser()->getStartPage();
        if (
            $success
            && $startpage  !== null
            && $startpage !== ModuleViewEnum::MODULE_VIEW_INDEX
        ) {
            $game->redirectTo(sprintf('/%s.php', $startpage));
        }
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
