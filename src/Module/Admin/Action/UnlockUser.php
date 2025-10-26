<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Admin\View\Playerlist\Playerlist;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserLockRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class UnlockUser implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UNLOCK_USER';

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserLockRepositoryInterface $userLockRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Playerlist::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $userIdToUnlock = request::getIntFatal('uid');
        $user = $this->userRepository->find($userIdToUnlock);
        if ($user === null) {
            throw new SanityCheckException(sprintf('userId %d does not exist', $userIdToUnlock));
        }

        $lock = $this->userLockRepository->getActiveByUser($user);
        if ($lock === null) {
            return;
        }

        $user = $lock->getUser();
        if ($user === null) {
            return;
        }

        $lock->setUser(null);
        $lock->setFormerUserId($user->getId());
        $this->userLockRepository->save($lock);

        $game->getInfo()->addInformationf(_('Der Spieler %s (%d) ist nun nicht mehr gesperrt'), $user->getName(), $userIdToUnlock);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
