<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

final class DatabaseDeletionHandler implements PlayerDeletionHandlerInteface
{
    private $databaseUserRepository;

    public function __construct(
        DatabaseUserRepositoryInterface $databaseUserRepository
    ) {
        $this->databaseUserRepository = $databaseUserRepository;
    }

    public function delete(UserInterface $user): void
    {
        $this->databaseUserRepository->truncateByUserId($user->getId());
    }
}
