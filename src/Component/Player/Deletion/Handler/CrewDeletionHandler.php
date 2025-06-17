<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

final class CrewDeletionHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private CrewRepositoryInterface $crewRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function delete(UserInterface $user): void
    {
        $this->crewAssignmentRepository->truncateByUser($user);
        $this->crewRepository->truncateByUser($user->getId());

        $this->entityManager->flush();
    }
}
