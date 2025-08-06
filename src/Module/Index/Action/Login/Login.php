<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Login;

use Override;
use Stu\Component\Game\RedirectionException;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\LoginException;
use Stu\Lib\Session\SessionLoginInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class Login implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_LOGIN';

    public function __construct(
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly LoginRequestInterface $loginRequest,
        private readonly SessionLoginInterface $sessionLogin
    ) {}

    /**
     * @throws LoginException
     */
    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $success = $this->sessionLogin->login(
            $this->loginRequest->getLoginName(),
            $this->loginRequest->getPassword()
        );

        if ($success) {
            $view = $this->userSettingsProvider->getDefaultView($game->getUser());
            throw new RedirectionException(sprintf('/%s', $view->getPhpPage()));
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
