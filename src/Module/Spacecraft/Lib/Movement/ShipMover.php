<?php

namespace Stu\Module\Spacecraft\Lib\Movement;

use Override;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ShipMover implements ShipMoverInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private FlightCompanyFactory $flightCompanyFactory,
        private ShipMovementInformationAdderInterface $shipMovementInformationAdder,
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

        $flightCompany = $this->flightCompanyFactory->create($leadWrapper);
        $initialTractoredShips = $this->initTractoredShips($flightCompany);

        // fly until destination arrived
        $hasTravelled = $this->travelFlightRoute(
            $flightCompany,
            $flightRoute,
            $messages
        );

        //skip save and log info if flight did not happen
        if (!$hasTravelled) {
            return $messages;
        }

        // save all ships
        $this->saveShips($flightCompany, $initialTractoredShips);

        // add post flight informations
        $this->postFlightInformations(
            $flightCompany,
            $flightRoute,
            $messages
        );

        return $messages;
    }

    private function travelFlightRoute(
        FlightCompany $flightCompany,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): bool {

        $hasTravelled = false;

        while (!$flightRoute->isDestinationArrived()) {
            $nextWaypoint = $flightRoute->getNextWaypoint();

            // nächstes Feld nicht passierbar
            if (!$nextWaypoint->getFieldType()->getPassable()) {
                $flightRoute->abortFlight();
                $messages->addInformation('Das nächste Feld kann nicht passiert werden');
                break;
            }

            if (!$flightCompany->isFlightPossible($flightRoute, $messages)) {
                $flightRoute->abortFlight();
                break;
            }

            if ($flightCompany->hasToLeaveFleet()) {
                $this->leaveFleet($flightCompany->getLeader(), $messages);
            }

            /** @var array<array{0: SpacecraftInterface, 1: ShipWrapperInterface}> */
            $movedTractoredShipWrappers = [];

            // move every possible ship by one field
            $this->moveShipsByOneField(
                $flightCompany,
                $flightRoute,
                $movedTractoredShipWrappers,
                $messages
            );
            $hasTravelled = true;

            // alert reaction check
            if (!$flightCompany->isEmpty()) {
                $this->alertReactionCheck(
                    $flightCompany->getLeadWrapper(),
                    $movedTractoredShipWrappers,
                    $messages
                );
            }

            if ($flightCompany->isEmpty()) {
                $flightRoute->abortFlight();
            }
        }

        return $hasTravelled;
    }

    /**
     * @param array<array{0: SpacecraftInterface, 1: ShipWrapperInterface}> $movedTractoredShipWrappers
     */
    private function moveShipsByOneField(
        FlightCompany $flightCompany,
        FlightRouteInterface $flightRoute,
        array &$movedTractoredShipWrappers,
        MessageCollectionInterface $messages
    ): void {

        $flightRoute->enterNextWaypoint(
            $flightCompany,
            $messages
        );

        foreach ($flightCompany->getActiveMembers() as $wrapper) {

            $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
            if ($tractoredShipWrapper !== null) {
                $movedTractoredShipWrappers[] = [$wrapper->get(), $tractoredShipWrapper];
            }
        }
    }

    /** @param array<array{0: SpacecraftInterface, 1: ShipWrapperInterface}> $movedTractoredShipWrappers */
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
        foreach ($movedTractoredShipWrappers as [$tractoringSpacecraft, $tractoredShipWrapper]) {
            if (!$tractoredShipWrapper->get()->isDestroyed()) {
                $alertRedInformations = new InformationWrapper();
                $this->alertReactionFacade->doItAll(
                    $tractoredShipWrapper,
                    $alertRedInformations,
                    $tractoringSpacecraft
                );

                if (!$alertRedInformations->isEmpty()) {
                    $this->addInformationMerge($alertRedInformations->getInformations(), $messages);
                }
            }
        }
    }

    /**
     * @return array<ShipInterface>
     */
    private function initTractoredShips(FlightCompany $flightCompany): array
    {
        $tractoredShips = [];

        foreach ($flightCompany->getActiveMembers() as $fleetShipWrapper) {
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

    private function leaveFleet(SpacecraftInterface $ship, MessageCollectionInterface $messages): void
    {
        if ($ship instanceof ShipInterface) {
            if ($this->leaveFleet->leaveFleet($ship)) {
                $messages->addInformationf('Die %s hat die Flotte verlassen', $ship->getName());
            }
        }
    }

    /**
     * @param array<ShipInterface> $initialTractoredShips
     */
    private function saveShips(FlightCompany $flightCompany, array $initialTractoredShips): void
    {
        foreach ($flightCompany->getActiveMembers() as $wrapper) {
            $ship = $wrapper->get();
            $this->spacecraftRepository->save($ship);
        }

        foreach ($initialTractoredShips as $tractoredShip) {
            $this->spacecraftRepository->save($tractoredShip);
        }
    }

    private function postFlightInformations(
        FlightCompany $flightCompany,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $wrappers = $flightCompany->getActiveMembers();

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

        $leadSpacecraft = $flightCompany->getLeader();
        $leadSpacecraftName = $leadSpacecraft->getName();
        $isFleetMode = $flightCompany->isFleetMode();

        //add destination info
        if ($flightCompany->isEverybodyDestroyed()) {
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

        $finalDestination = $leadSpacecraft->getLocation();

        //add info about anomalies
        foreach ($finalDestination->getAnomalies() as $anomaly) {
            $messages->addInformationf(
                '[b][color=yellow]In diesem Sektor befindet sich eine %s-Anomalie[/color][/b]',
                $anomaly->getAnomalyType()->getName()
            );
        }
        // add info about buyos
        foreach ($finalDestination->getBuoys() as $buoy) {
            $messages->addInformationf('[b][color=yellow]Boje entdeckt: [/color][/b]%s', $buoy->getText());
        }
    }

    /**
     * @param array<string> $value
     */
    private function addInformationMerge(array $value, MessageCollectionInterface $messages): void
    {
        $messages->add($this->messageFactory->createMessage(UserEnum::USER_NOONE, null, $value));
    }
}
