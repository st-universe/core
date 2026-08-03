<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class UserCrewRankDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private UserCrewRankRepositoryInterface $userCrewRankRepository) {}

    #[\Override]
    public function delete(User $user): void
    {
        $this->userCrewRankRepository->truncateByUser($user);
    }
}
