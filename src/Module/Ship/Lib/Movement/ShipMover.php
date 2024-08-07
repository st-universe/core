<?php

namespace Stu\Module\Ship\Lib\Movement;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\Message\MessageCollection;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\PreFlightConditionsCheckInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

//TODO unit tests
final class ShipMover implements ShipMoverInterface
{
    private MessageCollectionInterface $messages;

    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private ShipMovementInformationAdderInterface $shipMovementInformationAdder,
        private PreFlightConditionsCheckInterface $preFlightConditionsCheck,
        private LeaveFleetInterface $leaveFleet,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private MessageFactoryInterface $messageFactory
    ) {
        $this->messages = new MessageCollection();
    }

    private function addInformation(string $value): void
    {
        $this->addInformationMerge([$value]);
    }

    /**
     * @param array<string> $value
     */
    private function addInformationMerge(array $value): void
    {
        $this->messages->add($this->messageFactory->createMessage(UserEnum::USER_NOONE, null, $value));
    }

    #[Override]
    public function checkAndMove(
        ShipWrapperInterface $leadShipWrapper,
        FlightRouteInterface $flightRoute
    ): MessageCollectionInterface {

        $leadShip = $leadShipWrapper->get();
        $leadShipName = $leadShip->getName();
        $fleetWrapper = $leadShipWrapper->getFleetWrapper();

        $isFleetMode = $leadShip->isFleetLeader();
        $hasToLeaveFleet = $fleetWrapper !== null && !$isFleetMode;

        $wrappers = $isFleetMode && $fleetWrapper !== null
            ? $fleetWrapper->getShipWrappers()
            : new ArrayCollection([$leadShipWrapper->get()->getId() => $leadShipWrapper]);

        $isFixedFleetMode = $isFleetMode
            && $fleetWrapper !== null
            && $fleetWrapper->get()->isFleetFixed();

        $initialTractoredShips = $this->initTractoredShips($wrappers);

        $hasTravelled = false;

        // fly until destination arrived
        while (!$flightRoute->isDestinationArrived()) {
            $nextWaypoint = $flightRoute->getNextWaypoint();

            // nächstes Feld nicht passierbar
            if (!$nextWaypoint->getFieldType()->getPassable()) {
                $flightRoute->abortFlight();
                $this->addInformation(_('Das nächste Feld kann nicht passiert werden'));
                break;
            }

            $activeWrappers = $wrappers->filter(fn (ShipWrapperInterface $wrapper): bool => !$wrapper->get()->isDestroyed());

            // check all flight pre conditions
            $conditionCheckResult = $this->preFlightConditionsCheck->checkPreconditions(
                $leadShipWrapper,
                $activeWrappers->toArray(),
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

            $movedTractoredShipWrappers = [];

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

                        $movedTractoredShipWrappers[] = [$wrapper->get(), $tractoredShipWrapper];
                    }

                    $hasTravelled = true;
                }
            }

            $flightRoute->stepForward();

            // alert red check
            $alertRedInformations = new InformationWrapper();
            $this->alertReactionFacade->doItAll($leadShipWrapper, $alertRedInformations);

            if (!$alertRedInformations->isEmpty()) {
                $this->addInformationMerge($alertRedInformations->getInformations());
            }

            // alert red check for tractored ships
            foreach ($movedTractoredShipWrappers as [$tractoringShip, $tractoredShipWrapper]) {
                if (!$tractoringShip->isDestroyed()) {
                    $alertRedInformations = new InformationWrapper();
                    $this->alertReactionFacade->doItAll(
                        $tractoredShipWrapper,
                        $alertRedInformations,
                        $tractoringShip
                    );

                    if (!$alertRedInformations->isEmpty()) {
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
        // add info about buyos
        foreach ($leadShipWrapper->get()->getCurrentMapField()->getBuoys() as $buoy) {
            $this->addInformation(sprintf('[b][color=yellow]Boje entdeckt: [/color][/b]%s', $buoy->getText()));
        }

        return $this->messages;
    }

    /**
     * @param Collection<int, ShipWrapperInterface> $wrappers
     *
     * @return array<ShipInterface>
     */
    private function initTractoredShips(Collection $wrappers): array
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
     * @param Collection<int, ShipWrapperInterface> $wrappers
     */
    private function areAllShipsDestroyed(Collection $wrappers): bool
    {
        return !$wrappers->exists(fn (int $key, ShipWrapperInterface $wrapper): bool => !$wrapper->get()->isDestroyed());
    }
}
