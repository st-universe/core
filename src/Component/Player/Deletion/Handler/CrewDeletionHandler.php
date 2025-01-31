<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

final class CrewDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(private CrewAssignmentRepositoryInterface $shipCrewRepository, private CrewRepositoryInterface $crewRepository) {}

    #[Override]
    public function delete(UserInterface $user): void
    {
        $this->shipCrewRepository->truncateByUser($user->getId());
        $this->crewRepository->truncateByUser($user->getId());
    }
}
