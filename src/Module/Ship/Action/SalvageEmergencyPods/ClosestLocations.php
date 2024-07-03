<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class ClosestLocations
{
    public function __construct(private DistanceCalculationInterface $distanceCalculation, private ShipRepositoryInterface $shipRepository, private TroopTransferUtilityInterface $troopTransferUtility, private ColonyLibFactoryInterface $colonyLibFactory)
    {
    }

    /**
     * @return null|array{0: int, 1: ShipInterface}
     */
    public function searchClosestUsableStation(ShipInterface $ship, int $count): ?array
    {
        $result = null;

        $stations = $this->shipRepository->getStationsByUser($ship->getUser()->getId());
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
    public function searchClosestUsableColony(ShipInterface $ship, int $count): ?array
    {
        $result = null;

        $colonies = $ship->getUser()->getColonies();
        foreach ($colonies as $colony) {
            $crewLimit = $this->colonyLibFactory->createColonyPopulationCalculator(
                $colony
            )->getCrewLimit();
            $freeQuarters = $crewLimit - $colony->getCrewAssignmentAmount();

            if ($freeQuarters >= $count) {
                $distance = $this->distanceCalculation->shipToColonyDistance($ship, $colony);

                //add one distance if outside of system
                if ($ship->getSystem() === null) {
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
