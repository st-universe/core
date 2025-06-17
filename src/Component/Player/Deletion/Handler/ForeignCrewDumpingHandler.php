<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

final class ForeignCrewDumpingHandler implements PlayerDeletionHandlerInterface
{
    public function __construct(
        private StationRepositoryInterface $stationRepository,
        private SpacecraftLeaverInterface $spacecraftLeaver,
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function delete(UserInterface $user): void
    {
        foreach ($this->stationRepository->getStationsByUser($user->getId()) as $station) {

            foreach ($station->getCrewAssignments() as $crewAssignment) {

                $crew = $crewAssignment->getCrew();
                if ($crew->getUser() === $station->getUser()) {
                    continue;
                }

                $this->spacecraftLeaver->dumpCrewman(
                    $crewAssignment,
                    sprintf(
                        'Die Dienste von Crewman %s werden nicht mehr auf der Station %s von Spieler %s benötigt.',
                        $crew->getName(),
                        $station->getName(),
                        $station->getUser()->getName(),
                    )
                );

                $this->entityManager->detach($crewAssignment);
                $this->entityManager->detach($crew);
            }
        }
    }
}
