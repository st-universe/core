<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SalvageEmergencyPods;

use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

final class TransferToClosestLocation
{
    public function __construct(
        private ClosestLocations $closestLocations,
        private DistanceCalculationInterface $distanceCalculation,
        private CrewAssignmentRepositoryInterface $shipCrewRepository
    ) {}

    public function transfer(
        Spacecraft $ship,
        Spacecraft $target,
        int $crewCount,
        TradePost $closestTradepost
    ): string {
        $closestColony = null;
        $colonyDistance = null;

        $closestColonyArray = $this->closestLocations->searchClosestUsableColony($ship, $crewCount);
        if ($closestColonyArray !== null) {
            [$colonyDistance, $closestColony] = $closestColonyArray;
        }

        $stationDistance = null;
        $closestStation = null;
        $closestStationArray = $this->closestLocations->searchClosestUsableStation($ship, $crewCount);
        if ($closestStationArray !== null) {
            [$stationDistance, $closestStation] = $closestStationArray;
        }


        $tradepostDistance = $this->distanceCalculation->shipToShipDistance($ship, $closestTradepost->getStation());
        $minimumDistance = $this->getMinimumDistance($colonyDistance, $stationDistance, $tradepostDistance);

        //transfer to closest colony
        if ($colonyDistance === $minimumDistance && $closestColony !== null) {
            foreach ($target->getCrewAssignments() as $crewAssignment) {
                if ($crewAssignment->getCrew()->getUser() === $ship->getUser()) {
                    $crewAssignment->setColony($closestColony);
                    $crewAssignment->setSpacecraft(null);
                    $this->shipCrewRepository->save($crewAssignment);
                }
            }
            return sprintf(
                _('Deine Crew wurde geborgen und an die Kolonie "%s" (%s) überstellt'),
                $closestColony->getName(),
                $closestColony->getSectorString()
            );
        }

        //transfer to closest station
        if ($stationDistance === $minimumDistance && $closestStation !== null) {
            foreach ($target->getCrewAssignments() as $crewAssignment) {
                if ($crewAssignment->getCrew()->getUser() === $ship->getUser()) {
                    $crewAssignment->setSpacecraft($closestStation);
                    $this->shipCrewRepository->save($crewAssignment);
                }
            }
            return sprintf(
                _('Deine Crew wurde geborgen und an die Station "%s" (%s) überstellt'),
                $closestStation->getName(),
                $closestStation->getSectorString()
            );
        }

        //transfer to closest tradepost
        foreach ($target->getCrewAssignments() as $crewAssignment) {
            if ($crewAssignment->getCrew()->getUser() === $ship->getUser()) {
                $crewAssignment->setSpacecraft(null);
                $crewAssignment->setTradepost($closestTradepost);
                $this->shipCrewRepository->save($crewAssignment);
            }
        }
        return sprintf(
            _('Deine Crew wurde geborgen und an den Handelsposten "%s" (%s) überstellt'),
            $closestTradepost->getName(),
            $closestTradepost->getStation()->getSectorString()
        );
    }

    private function getMinimumDistance(?int $colonyDistance, ?int $stationDistance, int $tradePostDistance): int
    {
        return min(
            $colonyDistance ?? PHP_INT_MAX,
            $stationDistance ?? PHP_INT_MAX,
            $tradePostDistance
        );
    }
}
