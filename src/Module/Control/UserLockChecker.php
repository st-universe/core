<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Lib\UserLockedException;
use Stu\Lib\Session\SessionInterface;
use Stu\Orm\Entity\User;

final class UserLockChecker implements UserLockCheckerInterface
{
    public function __construct(
        private readonly SessionInterface $session
    ) {}

    #[\Override]
    public function check(User $user): void
    {
        $userLock = $user->getUserLock();
        if (!$user->isLocked() || $userLock === null) {
            return;
        }

        $this->session->logout();

        throw new UserLockedException(
            _('Dein Spieleraccount wurde gesperrt'),
            sprintf(
                _('Dein Spieleraccount ist noch für %d Ticks gesperrt. Begründung: %s'),
                $userLock->getRemainingTicks(),
                $userLock->getReason()
            )
        );
    }
}
