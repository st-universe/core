<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;

final class CrewDeletionHandler implements PlayerDeletionHandlerInterface
{
    private ShipCrewRepositoryInterface $shipCrewRepository;

    private CrewRepositoryInterface $crewRepository;

    public function __construct(
        ShipCrewRepositoryInterface $shipCrewRepository,
        CrewRepositoryInterface $crewRepository
    ) {
        $this->shipCrewRepository = $shipCrewRepository;
        $this->crewRepository = $crewRepository;
    }

    public function delete(UserInterface $user): void
    {
        $this->shipCrewRepository->truncateByUser($user->getId());
        $this->crewRepository->truncateByUser($user->getId());
    }
}
