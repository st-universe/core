<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony\Component;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class LimitCrewAssignments implements ColonyTickComponentInterface
{
    public function __construct(
        private readonly ColonyLibFactoryInterface $colonyLibFactory,
        private readonly CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private readonly CrewRepositoryInterface $crewRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * @param array<int, ColonyProduction> $production
     */
    #[\Override]
    public function work(Colony $colony, array &$production, InformationInterface $information): void
    {
        $crewToQuit = $this->getCrewToQuit($colony, $production);

        if ($crewToQuit === 0) {
            return;
        }

        $this->lockUserForCrewRemoval($colony);

        $crewToQuit = $this->getCrewToQuit($colony, $production);
        if ($crewToQuit === 0) {
            return;
        }

        $amount = $this->letCrewQuit($colony, $crewToQuit);

        if ($amount > 0) {
            $information->addInformationf(
                'Wegen Überschreitung des lokalen Crewlimits haben %d Crewman ihren Dienst auf dieser Kolonie quittiert',
                $amount
            );
        }
    }

    private function lockUserForCrewRemoval(Colony $colony): void
    {
        if (!$this->entityManager->getConnection()->isTransactionActive()) {
            return;
        }

        // The spacecraft tick can also remove colony crew, so serialize per user and recalculate after waiting.
        $this->entityManager->lock($colony->getUser(), LockMode::PESSIMISTIC_WRITE);
    }

    /**
     * @param array<int, ColonyProduction> $production
     */
    private function getCrewToQuit(Colony $colony, array $production): int
    {
        $crewLimit = $this->colonyLibFactory->createColonyPopulationCalculator(
            $colony,
            $production
        )->getCrewLimit();

        return max(0, $this->crewAssignmentRepository->getAmountByColony($colony) - $crewLimit);
    }

    private function letCrewQuit(Colony $colony, int $crewToQuit): int
    {
        $amount = 0;

        foreach ($this->crewAssignmentRepository->getByColony($colony, $crewToQuit) as $crewAssignment) {
            $amount++;
            $crew = $crewAssignment->getCrew();
            $crewAssignment->clearAssignment();
            $this->crewAssignmentRepository->delete($crewAssignment);
            $this->crewRepository->delete($crew);
        }

        return $amount;
    }
}
