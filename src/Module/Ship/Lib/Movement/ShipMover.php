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
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\PreFlightConditionsCheckInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

//TODO unit tests
final class ShipMover implements ShipMoverInterface
{
    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private ShipMovementInformationAdderInterface $shipMovementInformationAdder,
        private PreFlightConditionsCheckInterface $preFlightConditionsCheck,
        private LeaveFleetInterface $leaveFleet,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[Override]
    public function checkAndMove(
        ShipWrapperInterface $leadShipWrapper,
        FlightRouteInterface $flightRoute
    ): MessageCollectionInterface {

        $messages = new MessageCollection();

        $leadShip = $leadShipWrapper->get();
        $leadShipName = $leadShip->getName();
        $isFleetMode = $leadShip->isFleetLeader();

        $wrappers = $this->initWrappers($leadShipWrapper, $isFleetMode);
        $initialTractoredShips = $this->initTractoredShips($wrappers);

        // fly until destination arrived
        $hasTravelled = $this->travelFlightRoute(
            $leadShipWrapper,
            $wrappers,
            $flightRoute,
            $messages
        );

        //skip save and log info if flight did not happen
        if (!$hasTravelled) {
            return $messages;
        }

        // save all ships
        $this->saveShips($wrappers, $initialTractoredShips);

        // add post flight informations
        $this->postFlightInformations(
            $leadShipWrapper,
            $leadShipName,
            $wrappers,
            $flightRoute,
            $isFleetMode,
            $messages
        );

        return $messages;
    }

    /** @return ArrayCollection<int, ShipWrapperInterface> */
    private function initWrappers(ShipWrapperInterface $leadShipWrapper, bool $isFleetMode): Collection
    {
        $fleetWrapper = $leadShipWrapper->getFleetWrapper();

        return
            $isFleetMode && $fleetWrapper !== null
            ? $fleetWrapper->getShipWrappers()
            : new ArrayCollection([$leadShipWrapper->get()->getId() => $leadShipWrapper]);
    }

    /** @param ArrayCollection<int, ShipWrapperInterface> $wrappers */
    private function travelFlightRoute(
        ShipWrapperInterface $leadShipWrapper,
        Collection $wrappers,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): bool {

        $hasTravelled = false;
        $isFleetMode = $leadShipWrapper->get()->isFleetLeader();
        $fleetWrapper = $leadShipWrapper->getFleetWrapper();
        $hasToLeaveFleet = $fleetWrapper !== null && !$isFleetMode;

        $isFixedFleetMode = $isFleetMode
            && $fleetWrapper !== null
            && $fleetWrapper->get()->isFleetFixed();

        while (!$flightRoute->isDestinationArrived()) {
            $nextWaypoint = $flightRoute->getNextWaypoint();

            // nächstes Feld nicht passierbar
            if (!$nextWaypoint->getFieldType()->getPassable()) {
                $flightRoute->abortFlight();
                $this->addInformation('Das nächste Feld kann nicht passiert werden', $messages);
                break;
            }

            $activeWrappers = $wrappers->filter(fn(ShipWrapperInterface $wrapper): bool => !$wrapper->get()->isDestroyed());

            // check all flight pre conditions
            $conditionCheckResult = $this->preFlightConditionsCheck->checkPreconditions(
                $leadShipWrapper,
                $activeWrappers->toArray(),
                $flightRoute,
                $isFixedFleetMode
            );

            if (!$conditionCheckResult->isFlightPossible()) {
                $flightRoute->abortFlight();
                $this->addInformation('Der Weiterflug wurde aus folgenden Gründen abgebrochen:', $messages);
                $this->addInformationMerge($conditionCheckResult->getInformations(), $messages);
                break;
            }

            $this->addInformationMerge($conditionCheckResult->getInformations(), $messages);

            $movedTractoredShipWrappers = [];

            // move every possible ship by one field
            $this->moveShipsByOneField(
                $activeWrappers,
                $flightRoute,
                $conditionCheckResult,
                $hasToLeaveFleet,
                $hasTravelled,
                $messages
            );

            // alert reaction check
            $this->alertReactionCheck(
                $leadShipWrapper,
                $movedTractoredShipWrappers,
                $messages
            );

            if ($this->areAllShipsDestroyed($activeWrappers)) {
                $flightRoute->abortFlight();
                $this->addInformation('Es wurden alle Schiffe zerstört', $messages);
            }
        }

        return $hasTravelled;
    }

    /**
     * @param ArrayCollection<int, ShipWrapperInterface> $activeWrappers
     */
    private function moveShipsByOneField(
        Collection $activeWrappers,
        FlightRouteInterface $flightRoute,
        ConditionCheckResult $conditionCheckResult,
        bool $hasToLeaveFleet,
        bool &$hasTravelled,
        MessageCollectionInterface $messages
    ): void {

        foreach ($activeWrappers as $wrapper) {

            $ship = $wrapper->get();

            if ($conditionCheckResult->isNotBlocked($ship)) {

                $this->leaveFleetIfNotFleetLeader($ship, $hasToLeaveFleet, $messages);

                $flightRoute->enterNextWaypoint(
                    $wrapper,
                    $messages
                );

                $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
                if ($tractoredShipWrapper !== null) {
                    $flightRoute->enterNextWaypoint(
                        $tractoredShipWrapper,
                        $messages
                    );

                    $movedTractoredShipWrappers[] = [$wrapper->get(), $tractoredShipWrapper];
                }

                $hasTravelled = true;
            }
        }

        $flightRoute->stepForward();
    }

    /** @param array<array{0: ShipInterface, 1: ShipWrapperInterface}> $movedTractoredShipWrappers */
    private function alertReactionCheck(
        ShipWrapperInterface $leadShipWrapper,
        array $movedTractoredShipWrappers,
        MessageCollectionInterface $messages
    ): void {
        $alertRedInformations = new InformationWrapper();
        $this->alertReactionFacade->doItAll($leadShipWrapper, $alertRedInformations);

        if (!$alertRedInformations->isEmpty()) {
            $this->addInformationMerge($alertRedInformations->getInformations(), $messages);
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
                    $this->addInformationMerge($alertRedInformations->getInformations(), $messages);
                }
            }
        }
    }

    /**
     * @param ArrayCollection<int, ShipWrapperInterface> $wrappers
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

    private function leaveFleetIfNotFleetLeader(ShipInterface $ship, bool $hasToLeaveFleet, MessageCollectionInterface $messages): void
    {
        if ($hasToLeaveFleet && $ship->getFleet() !== null) {
            $this->leaveFleet->leaveFleet($ship);
            $this->addInformation(sprintf('Die %s hat die Flotte verlassen', $ship->getName()), $messages);
        }
    }

    /**
     * @param ArrayCollection<int, ShipWrapperInterface> $wrappers
     * @param array<ShipInterface> $initialTractoredShips
     */
    private function saveShips(Collection $wrappers, array $initialTractoredShips): void
    {
        foreach ($wrappers as $wrapper) {
            $ship = $wrapper->get();
            if (!$ship->isDestroyed()) {
                $this->shipRepository->save($ship);
            }
        }

        foreach ($initialTractoredShips as $tractoredShip) {
            $this->shipRepository->save($tractoredShip);
        }
    }

    /**
     * @param ArrayCollection<int, ShipWrapperInterface> $wrappers
     */
    private function postFlightInformations(
        ShipWrapperInterface $leadShipWrapper,
        string $leadShipName,
        Collection $wrappers,
        FlightRouteInterface $flightRoute,
        bool $isFleetMode,
        MessageCollectionInterface $messages
    ): void {

        //add tractor info
        foreach ($wrappers as $wrapper) {
            $ship = $wrapper->get();

            $tractoredShip = $ship->getTractoredShip();
            if ($tractoredShip !== null) {
                $this->shipMovementInformationAdder->pulledTractoredShip(
                    $ship,
                    $tractoredShip,
                    $flightRoute->getRouteMode(),
                    $messages
                );
            }
        }

        $leadShip = $leadShipWrapper->get();

        //add destination info
        if ($this->areAllShipsDestroyed($wrappers)) {
            $this->shipMovementInformationAdder->reachedDestinationDestroyed(
                $leadShip,
                $leadShipName,
                $isFleetMode,
                $flightRoute->getRouteMode(),
                $messages
            );
        } else {
            $this->shipMovementInformationAdder->reachedDestination(
                $leadShip,
                $isFleetMode,
                $flightRoute->getRouteMode(),
                $messages
            );
        }

        //add info about anomalies
        foreach ($leadShipWrapper->get()->getLocation()->getAnomalies() as $anomaly) {
            $this->addInformation(sprintf(
                '[b][color=yellow]In diesem Sektor befindet sich eine %s[/color][/b]',
                $anomaly->getAnomalyType()->getName()
            ), $messages);
        }
        // add info about buyos
        foreach ($leadShipWrapper->get()->getCurrentMapField()->getBuoys() as $buoy) {
            $this->addInformation(sprintf('[b][color=yellow]Boje entdeckt: [/color][/b]%s', $buoy->getText()), $messages);
        }
    }

    /**
     * @param Collection<int, ShipWrapperInterface> $wrappers
     */
    private function areAllShipsDestroyed(Collection $wrappers): bool
    {
        return !$wrappers->exists(fn(int $key, ShipWrapperInterface $wrapper): bool => !$wrapper->get()->isDestroyed());
    }

    private function addInformation(string $value, MessageCollectionInterface $messages): void
    {
        $this->addInformationMerge([$value], $messages);
    }

    /**
     * @param array<string> $value
     */
    private function addInformationMerge(array $value, MessageCollectionInterface $messages): void
    {
        $messages->add($this->messageFactory->createMessage(UserEnum::USER_NOONE, null, $value));
    }
}
