<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RouteModeEnum;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Component\Ship\ShipEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class EnoughEpsCondition implements PreFlightConditionInterface
{
    public function __construct(private SpacecraftSystemManagerInterface $spacecraftSystemManager) {}

    #[Override]
    public function check(
        SpacecraftWrapperInterface $wrapper,
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

    private function getEnergyForSystemsActivation(FlightRouteInterface $flightRoute, SpacecraftInterface $spacecraft): int
    {
        $result = 0;

        if ($flightRoute->isImpulseDriveNeeded()) {
            $result += $this->getEnergyUsageForActivation($spacecraft, SpacecraftSystemTypeEnum::IMPULSEDRIVE);
        }

        if ($flightRoute->isWarpDriveNeeded()) {
            $result += $this->getEnergyUsageForActivation($spacecraft, SpacecraftSystemTypeEnum::WARPDRIVE);
        }

        if ($flightRoute->isTranswarpCoilNeeded()) {
            $result += $this->getEnergyUsageForActivation($spacecraft, SpacecraftSystemTypeEnum::TRANSWARP_COIL);
        }

        if ($spacecraft instanceof ShipInterface && $spacecraft->getDockedTo() !== null) {
            $result += ShipEnum::SYSTEM_ECOST_DOCK;
        }

        return $result;
    }

    private function getEnergyUsageForActivation(SpacecraftInterface $spacecraft, SpacecraftSystemTypeEnum $systemId): int
    {
        if (!$spacecraft->hasShipSystem($systemId)) {
            return 0;
        }

        if (!$spacecraft->getSystemState($systemId)) {
            return $this->spacecraftSystemManager->getEnergyUsageForActivation($systemId);
        }

        return 0;
    }

    private function getEpsNeededForFlight(FlightRouteInterface $flightRoute, SpacecraftInterface $spacecraft): int
    {
        if ($flightRoute->getRouteMode() !== RouteModeEnum::ROUTE_MODE_FLIGHT) {
            return 0;
        }

        $nextWaypoint = $flightRoute->getNextWaypoint();
        if ($nextWaypoint instanceof MapInterface) {
            return 0;
        }

        if (!$spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::IMPULSEDRIVE)) {
            return 0;
        }

        $result = $spacecraft->getRump()->getFlightEcost();

        $tractoredShip = $spacecraft->getTractoredShip();
        if ($tractoredShip !== null) {
            $result += $tractoredShip->getRump()->getFlightEcost();
        }

        return $result;
    }
}
