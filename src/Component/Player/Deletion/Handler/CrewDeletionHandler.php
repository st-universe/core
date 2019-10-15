<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class CrewDeletionHandler implements PlayerDeletionHandlerInteface
{
    private $crewRepository;

    public function __construct(
        CrewRepositoryInterface $crewRepository
    ) {
        $this->crewRepository = $crewRepository;
    }

    public function delete(UserInterface $user): void
    {
        $this->crewRepository->truncateByUser($user->getId());
    }
}
