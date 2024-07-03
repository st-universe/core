<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight\Condition;

use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;

class EnoughEpsCondition implements PreFlightConditionInterface
{
    public function __construct(private ShipSystemManagerInterface $shipSystemManager)
    {
    }

    #[Override]
    public function check(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        ConditionCheckResult $conditionCheckResult
    ): void {

        $ship = $wrapper->get();

        $neededEps = $this->getEnergyForSystemsActivation($flightRoute, $ship)
            + $this->getEpsNeededForFlight($flightRoute, $ship);

        if ($neededEps === 0) {
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < $neededEps) {

            $conditionCheckResult->addBlockedShip(
                $ship,
                sprintf(
                    'Die %s hat nicht genug Energie für den %s (%d benötigt)',
                    $ship->getName(),
                    $ship->isTractoring() ? 'Traktor-Flug' : 'Flug',
                    $neededEps
                )
            );
        }
    }

    private function getEnergyForSystemsActivation(FlightRouteInterface $flightRoute, ShipInterface $ship): int
    {
        $result = 0;

        if ($flightRoute->isImpulseDriveNeeded()) {
            $result += $this->getEnergyUsageForActivation($ship, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE);
        }

        if ($flightRoute->isWarpDriveNeeded()) {
            $result += $this->getEnergyUsageForActivation($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
        }

        if ($flightRoute->isTranswarpCoilNeeded()) {
            $result += $this->getEnergyUsageForActivation($ship, ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL);
        }

        return $result;
    }

    private function getEnergyUsageForActivation(ShipInterface $ship, ShipSystemTypeEnum $systemId): int
    {
        if (!$ship->hasShipSystem($systemId)) {
            return 0;
        }

        if (!$ship->getSystemState($systemId)) {
            return $this->shipSystemManager->getEnergyUsageForActivation($systemId);
        }

        return 0;
    }

    private function getEpsNeededForFlight(FlightRouteInterface $flightRoute, ShipInterface $ship): int
    {
        if ($flightRoute->getRouteMode() !== RouteModeEnum::ROUTE_MODE_FLIGHT) {
            return 0;
        }

        $nextWaypoint = $flightRoute->getNextWaypoint();
        if ($nextWaypoint instanceof MapInterface) {
            return 0;
        }

        if (!$ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)) {
            return 0;
        }

        $result = $ship->getRump()->getFlightEcost();

        $tractoredShip = $ship->getTractoredShip();
        if ($tractoredShip !== null) {
            $result += $tractoredShip->getRump()->getFlightEcost();
        }

        return $result;
    }
}
