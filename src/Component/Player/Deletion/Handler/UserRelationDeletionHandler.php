<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRelationRepositoryInterface;

final class UserRelationDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private readonly UserRelationRepositoryInterface $userRelationRepository) {}

    #[\Override]
    public function delete(User $user): void
    {
        $this->userRelationRepository->truncateByUser($user);
    }
}
