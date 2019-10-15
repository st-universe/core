<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class ContactlistDeletionHandler implements PlayerDeletionHandlerInteface
{
    private $contactRepository;

    public function __construct(
        ContactRepositoryInterface $contactRepository
    ) {
        $this->contactRepository = $contactRepository;
    }

    public function delete(UserInterface $user): void
    {
        $userId = $user->getId();

        $this->contactRepository->truncateByUserAndOpponent($userId, $userId);
    }
}
