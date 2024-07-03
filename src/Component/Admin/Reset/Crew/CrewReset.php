<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Crew;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class CrewReset implements CrewResetInterface
{
    public function __construct(private CrewRepositoryInterface $crewRepository, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function deleteAllCrew(): void
    {
        echo "  - deleting all crew\n";

        $this->crewRepository->truncateAllCrew();

        $this->entityManager->flush();
    }
}
