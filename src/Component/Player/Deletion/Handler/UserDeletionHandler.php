<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserLockRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class UserDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private SessionStringRepositoryInterface $sessionStringRepository, private UserProfileVisitorRepositoryInterface $userProfileVisitorRepository, private UserRepositoryInterface $userRepository, private UserLockRepositoryInterface $userLockRepository)
    {
    }

    #[Override]
    public function delete(UserInterface $user): void
    {
        $this->unlockUser($user);
        $this->sessionStringRepository->truncate($user);
        $this->userProfileVisitorRepository->truncateByUser($user);

        // delete user
        $this->userRepository->delete($user);
    }

    private function unlockUser(UserInterface $user): void
    {
        $lock = $user->getUserLock();

        if ($lock === null) {
            return;
        }

        $lock->setUser(null);
        $lock->setUserId(null);
        $lock->setFormerUserId($user->getId());
        $this->userLockRepository->save($lock);
    }
}
