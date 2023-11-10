<?php

namespace Stu\Module\Ship\Lib\Movement;

use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollection;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\PreFlightConditionsCheckInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

//TODO unit tests
final class ShipMover implements ShipMoverInterface
{
    private ShipRepositoryInterface $shipRepository;

    private ShipMovementInformationAdderInterface $shipMovementInformationAdder;

    private PreFlightConditionsCheckInterface $preFlightConditionsCheck;

    private LeaveFleetInterface $leaveFleet;

    private AlertRedHelperInterface $alertRedHelper;

    private MessageCollectionInterface $messages;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipMovementInformationAdderInterface $shipMovementInformationAdder,
        PreFlightConditionsCheckInterface $preFlightConditionsCheck,
        LeaveFleetInterface $leaveFleet,
        AlertRedHelperInterface $alertRedHelper
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipMovementInformationAdder = $shipMovementInformationAdder;
        $this->preFlightConditionsCheck = $preFlightConditionsCheck;
        $this->leaveFleet = $leaveFleet;
        $this->alertRedHelper = $alertRedHelper;

        $this->messages = new MessageCollection();
    }

    private function addInformation(string $value): void
    {
        $this->messages->add(new Message(UserEnum::USER_NOONE, null, [$value]));
    }

    /**
     * @param array<string> $value
     */
    private function addInformationMerge(array $value): void
    {
        $this->messages->add(new Message(UserEnum::USER_NOONE, null, $value));
    }

    public function checkAndMove(
        ShipWrapperInterface $leadShipWrapper,
        FlightRouteInterface $flightRoute
    ): MessageCollectionInterface {

        $leadShip = $leadShipWrapper->get();
        $leadShipName = $leadShip->getName();
        $fleet = $leadShip->getFleet();

        $isFleetMode = $leadShip->isFleetLeader();
        $hasToLeaveFleet = $fleet !== null && !$isFleetMode;
        $fleetWrapper = $leadShipWrapper->getFleetWrapper();

        $wrappers = $isFleetMode && $fleetWrapper !== null ? $fleetWrapper->getShipWrappers() : [$leadShipWrapper];

        $isFixedFleetMode = $isFleetMode
            && $fleet !== null
            && $fleet->isFleetFixed();

        $initialTractoredShips = $this->initTractoredShips($wrappers);

        $hasTravelled = false;

        // fly until destination arrived
        while (!$flightRoute->isDestinationArrived()) {
            $nextWaypoint = $flightRoute->getNextWaypoint();

            // nächstes Feld nicht passierbar
            $nextFieldType = $nextWaypoint->getFieldType();
            if (!$nextFieldType->getPassable()) {
                $flightRoute->abortFlight();
                $this->addInformation(_('Das nächste Feld kann nicht passiert werden'));
                break;
            }

            $activeWrappers = array_filter(
                $wrappers,
                fn (ShipWrapperInterface $wrapper) => !$wrapper->get()->isDestroyed()
            );

            // check all flight pre conditions
            $conditionCheckResult = $this->preFlightConditionsCheck->checkPreconditions(
                $leadShipWrapper,
                $activeWrappers,
                $flightRoute,
                $isFixedFleetMode
            );

            if (!$conditionCheckResult->isFlightPossible()) {
                $flightRoute->abortFlight();
                $this->addInformation(_('Der Weiterflug wurde aus folgenden Gründen abgebrochen:'));
                $this->addInformationMerge($conditionCheckResult->getInformations());
                break;
            }

            $this->addInformationMerge($conditionCheckResult->getInformations());

            $movedTractoredShips = [];

            // move every ship by one field
            foreach ($activeWrappers as $wrapper) {

                $ship = $wrapper->get();

                if ($conditionCheckResult->isNotBlocked($ship)) {

                    $this->leaveFleetIfNotFleetLeader($ship, $hasToLeaveFleet);

                    $flightRoute->enterNextWaypoint(
                        $wrapper,
                        $this->messages
                    );

                    $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
                    if ($tractoredShipWrapper !== null) {
                        $flightRoute->enterNextWaypoint(
                            $tractoredShipWrapper,
                            $this->messages
                        );

                        $movedTractoredShips[] = [$wrapper->get(), $tractoredShipWrapper->get()];
                    }

                    $hasTravelled = true;
                }
            }

            $flightRoute->stepForward();

            // alert red check
            $alertRedInformations =
                $this->alertRedHelper->doItAll($leadShip);

            if ($alertRedInformations !== null) {
                $this->addInformationMerge($alertRedInformations->getInformations());
            }

            // alert red check for tractored ships
            foreach ($movedTractoredShips as [$tractoringShip, $tractoredShip]) {
                if (!$tractoredShip->isDestroyed()) {
                    $alertRedInformations =
                        $this->alertRedHelper->doItAll($tractoredShip, null, $tractoringShip);

                    if ($alertRedInformations !== null) {
                        $this->addInformationMerge($alertRedInformations->getInformations());
                    }
                }
            }

            if ($this->areAllShipsDestroyed($activeWrappers)) {
                $flightRoute->abortFlight();
                $this->addInformation(_('Es wurden alle Schiffe zerstört'));
            }
        }

        //skip save and log info if flight did not happen
        if (!$hasTravelled) {
            return $this->messages;
        }

        // save all ships
        foreach ($wrappers as $wrapper) {
            $ship = $wrapper->get();
            if (!$ship->isDestroyed()) {
                $this->shipRepository->save($ship);
            }

            $tractoredShip = $ship->getTractoredShip();
            if ($tractoredShip !== null) {
                $this->shipMovementInformationAdder->pulledTractoredShip(
                    $ship,
                    $tractoredShip,
                    $flightRoute->getRouteMode(),
                    $this->messages
                );
            }
        }


        foreach ($initialTractoredShips as $tractoredShip) {
            $this->shipRepository->save($tractoredShip);
        }

        if ($this->areAllShipsDestroyed($wrappers)) {
            $this->shipMovementInformationAdder->reachedDestinationDestroyed(
                $leadShip,
                $leadShipName,
                $isFleetMode,
                $flightRoute->getRouteMode(),
                $this->messages
            );
        } else {
            $this->shipMovementInformationAdder->reachedDestination(
                $leadShip,
                $isFleetMode,
                $flightRoute->getRouteMode(),
                $this->messages
            );
        }

        //add info about anomalies
        foreach ($leadShipWrapper->get()->getLocation()->getAnomalies() as $anomaly) {
            $this->addInformation(sprintf('[b][color=yellow]In diesem Sektor befindet sich eine %s[/color][/b]', $anomaly->getAnomalyType()->getName()));
        }

        return $this->messages;
    }

    /**
     * @param array<ShipWrapperInterface> $wrappers
     * 
     * @return array<ShipInterface>
     */
    private function initTractoredShips(array $wrappers): array
    {
        $tractoredShips = [];

        foreach ($wrappers as $fleetShipWrapper) {
            $fleetShip = $fleetShipWrapper->get();

            $tractoredShip = $fleetShip->getTractoredShip();
            if (
                $tractoredShip !== null
            ) {
                $tractoredShips[] = $tractoredShip;
            }
        }

        return $tractoredShips;
    }

    private function leaveFleetIfNotFleetLeader(ShipInterface $ship, bool $hasToLeaveFleet): void
    {
        if ($hasToLeaveFleet && $ship->getFleet() !== null) {
            $this->leaveFleet->leaveFleet($ship);
            $this->addInformation(sprintf('Die %s hat die Flotte verlassen', $ship->getName()));
        }
    }

    /**
     * @param ShipWrapperInterface[] $wrappers
     */
    private function areAllShipsDestroyed(array $wrappers): bool
    {
        foreach ($wrappers as $wrapper) {
            if (!$wrapper->get()->isDestroyed()) {
                return false;
            }
        }

        return true;
    }
}
