<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class ContactDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository
    ) {}

    #[\Override]
    public function delete(User $user): void
    {
        $this->contactRepository->truncateByUser($user->getId());
    }
}
