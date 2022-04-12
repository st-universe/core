<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use request;
use Stu\Lib\SessionInterface;
use Stu\Module\Admin\View\Playerlist\Playerlist;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class LockUser implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LOCK_USER';

    private UserRepositoryInterface $userRepository;

    private SessionInterface $session;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SessionInterface $session,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->userRepository = $userRepository;
        $this->session = $session;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Playerlist::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $userIdToLock = request::getIntFatal('uid');

        $userToLock = $this->userRepository->find($userIdToLock);

        if ($userToLock === null) {
            return;
        }

        //destroy session
        $this->session->logout($userToLock);

        $game->addInformationf(_('Der Spieler %s (%d) ist nun gesperrt'), $userToLock->getName(), $userIdToLock);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
