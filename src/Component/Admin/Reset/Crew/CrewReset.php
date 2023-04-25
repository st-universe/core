<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Crew;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class CrewReset implements CrewResetInterface
{
    private CrewRepositoryInterface $crewRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        CrewRepositoryInterface $crewRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->crewRepository = $crewRepository;
        $this->entityManager = $entityManager;
    }

    public function deleteAllCrew(): void
    {
        echo "  - deleting all crew\n";

        $this->crewRepository->truncateAllCrew();

        $this->entityManager->flush();
    }
}
