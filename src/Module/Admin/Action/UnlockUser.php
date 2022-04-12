<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use request;
use Stu\Module\Admin\View\Playerlist\Playerlist;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserLockRepositoryInterface;

final class UnlockUser implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_UNLOCK_USER';

    private UserLockRepositoryInterface $userLockRepository;

    public function __construct(
        UserLockRepositoryInterface $userLockRepository
    ) {
        $this->userLockRepository = $userLockRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Playerlist::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $userIdToUnlock = request::getIntFatal('uid');
        $lock = $this->userLockRepository->getActiveByUser($userIdToUnlock);

        if ($lock === null) {
            return;
        }

        $user = $lock->getUser();

        $lock->setUser(null);
        $lock->setUserId(null);
        $lock->setFormerUserId($user->getId());
        $this->userLockRepository->save($lock);

        $game->addInformationf(_('Der Spieler %s (%d) ist nun nicht mehr gesperrt'), $user->getName(), $userIdToUnlock);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
