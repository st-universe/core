<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;

final class TransferToClosestLocation
{
    public function __construct(private ClosestLocations $closestLocations, private DistanceCalculationInterface $distanceCalculation, private ShipCrewRepositoryInterface $shipCrewRepository)
    {
    }

    public function transfer(
        ShipInterface $ship,
        ShipInterface $target,
        int $crewCount,
        TradePostInterface $closestTradepost
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


        $tradepostDistance = $this->distanceCalculation->shipToShipDistance($ship, $closestTradepost->getShip());
        $minimumDistance = $this->getMinimumDistance($colonyDistance, $stationDistance, $tradepostDistance);

        //transfer to closest colony
        if ($colonyDistance === $minimumDistance && $closestColony !== null) {
            foreach ($target->getCrewAssignments() as $crewAssignment) {
                if ($crewAssignment->getCrew()->getUser() === $ship->getUser()) {
                    $crewAssignment->setColony($closestColony);
                    $crewAssignment->setShip(null);
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
                    $crewAssignment->setShip($closestStation);
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
                $crewAssignment->setShip(null);
                $crewAssignment->setTradepost($closestTradepost);
                $this->shipCrewRepository->save($crewAssignment);
            }
        }
        return sprintf(
            _('Deine Crew wurde geborgen und an den Handelsposten "%s" (%s) überstellt'),
            $closestTradepost->getName(),
            $closestTradepost->getShip()->getSectorString()
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
