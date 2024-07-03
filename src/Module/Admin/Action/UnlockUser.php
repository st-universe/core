<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Override;
use request;
use Stu\Module\Admin\View\Playerlist\Playerlist;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserLockRepositoryInterface;

final class UnlockUser implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UNLOCK_USER';

    public function __construct(private UserLockRepositoryInterface $userLockRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Playerlist::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $userIdToUnlock = request::getIntFatal('uid');
        $lock = $this->userLockRepository->getActiveByUser($userIdToUnlock);

        if ($lock === null) {
            return;
        }

        $user = $lock->getUser();

        if ($user === null) {
            return;
        }

        $lock->setUser(null);
        $lock->setUserId(null);
        $lock->setFormerUserId($user->getId());
        $this->userLockRepository->save($lock);

        $game->addInformationf(_('Der Spieler %s (%d) ist nun nicht mehr gesperrt'), $user->getName(), $userIdToUnlock);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
