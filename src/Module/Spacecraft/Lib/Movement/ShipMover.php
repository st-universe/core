<?php

namespace Stu\Module\Spacecraft\Lib\Movement;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\PreFlightConditionsCheckInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

//TODO unit tests
final class ShipMover implements ShipMoverInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ShipMovementInformationAdderInterface $shipMovementInformationAdder,
        private PreFlightConditionsCheckInterface $preFlightConditionsCheck,
        private LeaveFleetInterface $leaveFleet,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[Override]
    public function checkAndMove(
        SpacecraftWrapperInterface $leadWrapper,
        FlightRouteInterface $flightRoute
    ): MessageCollectionInterface {

        $messages = $this->messageFactory->createMessageCollection();

        $leadSpacecraft = $leadWrapper->get();
        $leadSpacecraftName = $leadSpacecraft->getName();
        $isFleetMode = $leadSpacecraft instanceof ShipInterface ? $leadSpacecraft->isFleetLeader() : false;

        $wrappers = $this->initWrappers($leadWrapper, $isFleetMode);
        $initialTractoredShips = $this->initTractoredShips($wrappers);

        // fly until destination arrived
        $hasTravelled = $this->travelFlightRoute(
            $leadWrapper,
            $wrappers,
            $isFleetMode,
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
            $leadWrapper,
            $leadSpacecraftName,
            $wrappers,
            $flightRoute,
            $isFleetMode,
            $messages
        );

        return $messages;
    }

    /** @return Collection<int, covariant SpacecraftWrapperInterface> */
    private function initWrappers(SpacecraftWrapperInterface $leadWrapper, bool $isFleetMode): Collection
    {
        $fleetWrapper = $leadWrapper->getFleetWrapper();

        return
            $isFleetMode && $fleetWrapper !== null
            ? $fleetWrapper->getShipWrappers()
            : new ArrayCollection([$leadWrapper->get()->getId() => $leadWrapper]);
    }

    /** @param Collection<int, covariant SpacecraftWrapperInterface> $wrappers */
    private function travelFlightRoute(
        SpacecraftWrapperInterface $leadWrapper,
        Collection $wrappers,
        bool $isFleetMode,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): bool {

        $hasTravelled = false;
        $fleetWrapper = $leadWrapper->getFleetWrapper();
        $hasToLeaveFleet = $fleetWrapper !== null && !$isFleetMode;

        $isFixedFleetMode = $isFleetMode
            && $fleetWrapper !== null
            && $fleetWrapper->get()->isFleetFixed();

        while (!$flightRoute->isDestinationArrived()) {
            $nextWaypoint = $flightRoute->getNextWaypoint();

            // nächstes Feld nicht passierbar
            if (!$nextWaypoint->getFieldType()->getPassable()) {
                $flightRoute->abortFlight();
                $messages->addInformation('Das nächste Feld kann nicht passiert werden');
                break;
            }

            $activeWrappers = $wrappers->filter(fn(SpacecraftWrapperInterface $wrapper): bool => !$wrapper->get()->isDestroyed());

            // check all flight pre conditions
            $conditionCheckResult = $this->preFlightConditionsCheck->checkPreconditions(
                $leadWrapper,
                $activeWrappers->toArray(),
                $flightRoute,
                $isFixedFleetMode
            );

            if (!$conditionCheckResult->isFlightPossible()) {
                $flightRoute->abortFlight();
                $messages->addInformation('Der Weiterflug wurde aus folgenden Gründen abgebrochen:');
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
                $leadWrapper,
                $movedTractoredShipWrappers,
                $messages
            );

            if ($this->areAllShipsDestroyed($activeWrappers)) {
                $flightRoute->abortFlight();
                $messages->addInformation('Es wurden alle Schiffe zerstört');
            }
        }

        return $hasTravelled;
    }

    /**
     * @param Collection<int, SpacecraftWrapperInterface> $activeWrappers
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

    /** @param array<array{0: ShipInterface, 1: SpacecraftWrapperInterface}> $movedTractoredShipWrappers */
    private function alertReactionCheck(
        SpacecraftWrapperInterface $leadWrapper,
        array $movedTractoredShipWrappers,
        MessageCollectionInterface $messages
    ): void {
        $alertRedInformations = new InformationWrapper();
        $this->alertReactionFacade->doItAll($leadWrapper, $alertRedInformations);

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
     * @param Collection<int, covariant SpacecraftWrapperInterface> $wrappers
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

    private function leaveFleetIfNotFleetLeader(SpacecraftInterface $ship, bool $hasToLeaveFleet, MessageCollectionInterface $messages): void
    {
        if ($hasToLeaveFleet && $ship instanceof ShipInterface) {
            if ($this->leaveFleet->leaveFleet($ship)) {
                $messages->addInformation(sprintf('Die %s hat die Flotte verlassen', $ship->getName()));
            }
        }
    }

    /**
     * @param Collection<int, covariant SpacecraftWrapperInterface> $wrappers
     * @param array<ShipInterface> $initialTractoredShips
     */
    private function saveShips(Collection $wrappers, array $initialTractoredShips): void
    {
        foreach ($wrappers as $wrapper) {
            $ship = $wrapper->get();
            if (!$ship->isDestroyed()) {
                $this->spacecraftRepository->save($ship);
            }
        }

        foreach ($initialTractoredShips as $tractoredShip) {
            $this->spacecraftRepository->save($tractoredShip);
        }
    }

    /**
     * @param Collection<int, covariant SpacecraftWrapperInterface> $wrappers
     */
    private function postFlightInformations(
        SpacecraftWrapperInterface $leadWrapper,
        string $leadSpacecraftName,
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

        $leadSpacecraft = $leadWrapper->get();

        //add destination info
        if ($this->areAllShipsDestroyed($wrappers)) {
            $this->shipMovementInformationAdder->reachedDestinationDestroyed(
                $leadSpacecraft,
                $leadSpacecraftName,
                $isFleetMode,
                $flightRoute->getRouteMode(),
                $messages
            );
        } else {
            $this->shipMovementInformationAdder->reachedDestination(
                $leadSpacecraft,
                $isFleetMode,
                $flightRoute->getRouteMode(),
                $messages
            );
        }

        //add info about anomalies
        foreach ($leadWrapper->get()->getLocation()->getAnomalies() as $anomaly) {
            $messages->addInformation(sprintf(
                '[b][color=yellow]In diesem Sektor befindet sich eine %s-Anomalie[/color][/b]',
                $anomaly->getAnomalyType()->getName()
            ));
        }
        // add info about buyos
        foreach ($leadWrapper->get()->getLocation()->getBuoys() as $buoy) {
            $messages->addInformation(sprintf('[b][color=yellow]Boje entdeckt: [/color][/b]%s', $buoy->getText()));
        }
    }

    /**
     * @param Collection<int, covariant SpacecraftWrapperInterface> $wrappers
     */
    private function areAllShipsDestroyed(Collection $wrappers): bool
    {
        return !$wrappers->exists(fn(int $key, SpacecraftWrapperInterface $wrapper): bool => !$wrapper->get()->isDestroyed());
    }

    /**
     * @param array<string> $value
     */
    private function addInformationMerge(array $value, MessageCollectionInterface $messages): void
    {
        $messages->add($this->messageFactory->createMessage(UserEnum::USER_NOONE, null, $value));
    }
}
