<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

final class UserMapDeletionHandler implements PlayerDeletionHandlerInterface
{
    private UserMapRepositoryInterface $userMapRepository;

    public function __construct(
        UserMapRepositoryInterface $userMapRepository
    ) {
        $this->userMapRepository = $userMapRepository;
    }

    public function delete(UserInterface $user): void
    {
        $this->userMapRepository->truncateByUser($user->getId());
    }
}
