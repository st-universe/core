<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SalvageEmergencyPods;

use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

class ClosestLocations
{
    public function __construct(
        private DistanceCalculationInterface $distanceCalculation,
        private StationRepositoryInterface $stationRepository,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private ColonyLibFactoryInterface $colonyLibFactory
    ) {}

    /**
     * @return null|array{0: int, 1: StationInterface}
     */
    public function searchClosestUsableStation(SpacecraftInterface $ship, int $count): ?array
    {
        $result = null;

        $stations = $this->stationRepository->getStationsByUser($ship->getUser()->getId());
        foreach ($stations as $station) {
            if (!$station->hasEnoughCrew()) {
                continue;
            }

            $freeQuarters = $this->troopTransferUtility->getFreeQuarters($station);
            if ($freeQuarters >= $count) {
                $distance = $this->distanceCalculation->shipToShipDistance($ship, $station);

                if ($result === null) {
                    $result = [];
                }

                if (empty($result) || $distance < $result[0]) {
                    $result = [$distance, $station];
                }
            }
        }

        return $result;
    }

    /**
     * @return null|array{0: int, 1: ColonyInterface}
     */
    public function searchClosestUsableColony(SpacecraftInterface $spacecraft, int $count): ?array
    {
        $result = null;

        $colonies = $spacecraft->getUser()->getColonies();
        foreach ($colonies as $colony) {
            $crewLimit = $this->colonyLibFactory->createColonyPopulationCalculator(
                $colony
            )->getCrewLimit();
            $freeQuarters = $crewLimit - $colony->getCrewAssignmentAmount();

            if ($freeQuarters >= $count) {
                $distance = $this->distanceCalculation->spacecraftToColonyDistance($spacecraft, $colony);

                //add one distance if outside of system
                if ($spacecraft->getSystem() === null) {
                    $distance += 1;
                }

                if ($result === null) {
                    $result = [];
                }

                if (empty($result) || $distance < $result[0]) {
                    $result = [$distance, $colony];
                }
            }
        }

        return $result;
    }
}
