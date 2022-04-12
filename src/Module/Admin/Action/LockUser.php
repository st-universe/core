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
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserLockRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class LockUser implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LOCK_USER';

    private UserRepositoryInterface $userRepository;

    private UserLockRepositoryInterface $userLockRepository;

    private SessionInterface $session;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserLockRepositoryInterface $userLockRepository,
        SessionInterface $session,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->userRepository = $userRepository;
        $this->userLockRepository = $userLockRepository;
        $this->session = $session;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->loggerUtil->init('admin', LoggerEnum::LEVEL_ERROR);
        $game->setView(Playerlist::VIEW_IDENTIFIER);

        $this->loggerUtil->log('A');
        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $this->loggerUtil->log('B');
        $userIdToLock = request::postIntFatal('uid');
        $remainingTicks = request::postInt('ticks');

        if (!$remainingTicks) {
            $game->addInformation(_('Bitte Anzahl Ticks angeben'));
            return;
        }

        $userToLock = $this->userRepository->find($userIdToLock);

        $this->loggerUtil->log('C');
        if ($userToLock === null) {
            $this->loggerUtil->log('D');
            return;
        }

        //setup lock
        $this->setUserLock($userToLock, $remainingTicks);

        $this->loggerUtil->log('E');
        //destroy session
        $this->session->logout($userToLock);
        $this->loggerUtil->log('F');

        //create user lock


        $game->addInformationf(_('Der Spieler %s (%d) ist nun gesperrt'), $userToLock->getName(), $userIdToLock);
    }

    private function setUserLock(UserInterface $user, int $remainingTicks): void
    {
        $lock = $this->userLockRepository->getByUser($user->getId());

        if ($lock === null) {
            $lock = $this->userLockRepository->prototype();
            $lock->setUser($user);
        }

        $lock->setRemainingTicks($remainingTicks);
        $reason = request::postString('reason');

        if ($reason) {
            $lock->setReason($reason);
        }
        $this->userLockRepository->save($lock);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
