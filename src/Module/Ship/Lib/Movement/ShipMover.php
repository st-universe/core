<?php

namespace Stu\Module\Ship\Lib\Movement;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Lib\DamageWrapper;
use Stu\Lib\InformationWrapper;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Entity\MapFieldTypeInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

//TODO unit tests
final class ShipMover implements ShipMoverInterface
{
    private ShipRepositoryInterface $shipRepository;

    private EntryCreatorInterface $entryCreator;

    private ShipRemoverInterface $shipRemover;

    private ShipSystemManagerInterface $shipSystemManager;

    private ApplyDamageInterface $applyDamage;

    private AlertRedHelperInterface $alertRedHelper;

    private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend;

    private TractorMassPayloadUtilInterface $tractorMassPayloadUtil;

    private ShipStateChangerInterface $shipStateChanger;

    private TholianWebUtilInterface $tholianWebUtil;

    private ShipMovementComponentsFactoryInterface $shipMovementComponentsFactory;

    private ShipMovementInformationAdderInterface $shipMovementInformationAdder;

    private LeaveFleetInterface $leaveFleet;

    private bool $fleetMode = false;

    /**
     * @var array<int, ShipInterface>
     */
    private array $lostShips = [];

    /**
     * @var array<int, array<ShipInterface>>
     */
    private array $tractoredShips = [];

    private bool $leaderMovedToNextField = false;
    private bool $hasTravelled = false;

    private InformationWrapper $informations;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover,
        ShipSystemManagerInterface $shipSystemManager,
        ApplyDamageInterface $applyDamage,
        AlertRedHelperInterface $alertRedHelper,
        CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend,
        TractorMassPayloadUtilInterface  $tractorMassPayloadUtil,
        ShipStateChangerInterface $shipStateChanger,
        TholianWebUtilInterface $tholianWebUtil,
        ShipMovementComponentsFactoryInterface $shipMovementComponentsFactory,
        ShipMovementInformationAdderInterface $shipMovementInformationAdder,
        LeaveFleetInterface $leaveFleet
    ) {
        $this->shipRepository = $shipRepository;
        $this->entryCreator = $entryCreator;
        $this->shipRemover = $shipRemover;
        $this->shipSystemManager = $shipSystemManager;
        $this->applyDamage = $applyDamage;
        $this->alertRedHelper = $alertRedHelper;
        $this->cancelColonyBlockOrDefend = $cancelColonyBlockOrDefend;
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
        $this->shipStateChanger = $shipStateChanger;
        $this->tholianWebUtil = $tholianWebUtil;
        $this->shipMovementComponentsFactory = $shipMovementComponentsFactory;
        $this->shipMovementInformationAdder = $shipMovementInformationAdder;
        $this->leaveFleet = $leaveFleet;
    }

    /**
     * sets member fleetMode = true if ship is in fleet and fleet leader
     */
    private function determineFleetMode(ShipInterface $leadShip): void
    {
        if ($leadShip->getFleet() === null) {
            return;
        }
        if (!$leadShip->isFleetLeader()) {
            return;
        }
        $this->setFleetMode(true);
    }

    private function setFleetMode(bool $value): void
    {
        $this->fleetMode = $value;
    }

    private function isFleetMode(): bool
    {
        return $this->fleetMode;
    }

    private function addInformation(?string $value): void
    {
        $this->informations->addInformation($value);
    }

    /**
     * @param array<string> $value
     */
    private function addInformationMerge(array $value): void
    {
        $this->informations->addInformationArray($value);
    }

    public function checkAndMove(
        ShipWrapperInterface $leadShipWrapper,
        FlightRouteInterface $flightRoute
    ): InformationWrapper {

        $this->informations = new InformationWrapper();
        $leadShip = $leadShipWrapper->get();
        if (
            $leadShip->isFleetLeader()
            && $leadShip->getFleet() !== null
            && $leadShip->getFleet()->getDefendedColony() !== null
        ) {
            $this->addInformation(_('Flug während Kolonie-Verteidigung nicht möglich'));

            return $this->informations;
        }

        if (
            $leadShip->isFleetLeader()
            && $leadShip->getFleet() !== null
            && $leadShip->getFleet()->getBlockedColony() !== null
        ) {
            $this->addInformation(_('Flug während Kolonie-Blockierung nicht möglich'));

            return $this->informations;
        }

        $this->determineFleetMode($leadShip);

        $wrappers = $this->isFleetMode() ? $this->alertRedHelper->getShips($leadShip) : [$leadShipWrapper];

        $isFixedFleetMode = $this->isFleetMode()
            && $leadShip->getFleet() !== null
            && $leadShip->getFleet()->isFleetFixed();
        $this->getReadyForFlight($leadShip, $wrappers, $isFixedFleetMode, $flightRoute);
        if ($this->lostShips !== []) {
            $this->addInformation(_('Der Weiterflug wurde abgebrochen!'));

            return $this->informations;
        }

        $this->initTractoredShips($wrappers);

        // fly until destination arrived
        while (!$flightRoute->isDestinationArrived()) {
            $this->leaderMovedToNextField = false;
            $nextWaypoint = $flightRoute->getNextWaypoint();

            if ($isFixedFleetMode) {
                $reasons = $this->shipMovementComponentsFactory->createShipMovementBlockingDeterminator()->determine($wrappers);

                if ($reasons !== []) {
                    $flightRoute->abortFlight();
                    $this->addInformation(_('Der Weiterflug wurde aus folgenden Gründen abgebrochen:'));
                    $this->addInformationMerge($reasons);
                    break;
                }
            }

            // move every ship by one field
            foreach ($wrappers as $wrapper) {
                if (
                    !array_key_exists($wrapper->get()->getId(), $this->lostShips)
                    && ($wrapper->get() === $leadShip || $this->leaderMovedToNextField)
                ) {
                    $this->moveOneField(
                        $leadShip,
                        $wrapper,
                        $nextWaypoint,
                        $flightRoute,
                        $isFixedFleetMode
                    );
                }
            }

            $flightRoute->stepForward();

            //if moving in warp skip AR check
            if ($leadShip->getWarpState()) {
                continue;
            }

            if (!$this->areShipsLeft($wrappers)) {
                continue;
            }

            //Alarm-Rot check
            $shipsToShuffle = $this->alertRedHelper->checkForAlertRedShips($leadShip, $this->informations);
            shuffle($shipsToShuffle);
            foreach ($shipsToShuffle as $alertShip) {
                // if there are ships left
                if ($this->areShipsLeft($wrappers)) {
                    $this->alertRedHelper->performAttackCycle($alertShip, $leadShip, $this->informations);
                } else {
                    break;
                }

                // check for destroyed ships
                foreach ($wrappers as $wrapper) {
                    if ($wrapper->get()->isDestroyed()) {
                        $this->addLostShip($wrapper, $leadShip, false, $flightRoute, null);
                    }
                }
            }

            //AR Check for tractored ships
            foreach ($this->tractoredShips as [$tractoringShip, $tractoredShip]) {
                if (!$tractoredShip->isDestroyed()) {
                    $alertRedInformations =
                        $this->alertRedHelper->doItAll($tractoredShip, null, $tractoringShip);

                    if ($alertRedInformations !== null) {
                        $this->addInformationMerge($alertRedInformations->getInformations());
                    }
                }
            }
        }

        //skip save and log info if flight did not happen
        if (!$this->hasTravelled) {
            return $this->informations;
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
                    $tractoredShip->getName(),
                    $flightRoute->getRouteMode(),
                    $this->informations
                );
                $this->shipRepository->save($tractoredShip);
            }
        }

        if ($this->areAllShipsDestroyed($wrappers)) {
            $this->shipMovementInformationAdder->reachedDestinationDestroyed(
                $leadShip,
                $this->isFleetMode(),
                $flightRoute->getRouteMode(),
                $this->informations
            );
        } else {
            $this->shipMovementInformationAdder->reachedDestination(
                $leadShip,
                $this->isFleetMode(),
                $flightRoute->getRouteMode(),
                $this->informations
            );
        }

        //add info about anomalies
        foreach ($leadShipWrapper->get()->getLocation()->getAnomalies() as $anomaly) {
            $this->addInformation(sprintf('[b][color=yellow]In diesem Sektor befindet sich eine %s[/color][/b]', $anomaly->getAnomalyType()->getName()));
        }

        return $this->informations;
    }

    /**
     * @param ShipWrapperInterface[] $wrappers
     */
    private function initTractoredShips(array $wrappers): void
    {
        foreach ($wrappers as $fleetShipWrapper) {
            $fleetShip = $fleetShipWrapper->get();

            $tractoredShip = $fleetShip->getTractoredShip();
            if (
                $tractoredShip !== null
                && !array_key_exists($fleetShip->getId(), $this->lostShips)
            ) {
                $this->tractoredShips[$fleetShip->getId()] = [$fleetShip, $tractoredShip];
            }
        }
    }

    /**
     * @param ShipWrapperInterface[] $wrappers
     */
    private function areShipsLeft(array $wrappers): bool
    {
        foreach ($wrappers as $wrapper) {
            if (!array_key_exists($wrapper->get()->getId(), $this->lostShips)) {
                return true;
            }
        }

        return false;
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

    /**
     * @param ShipWrapperInterface[] $wrappers
     */
    private function getReadyForFlight(ShipInterface $leadShip, array $wrappers, bool $isFixedFleetMode, FlightRouteInterface $flightRoute): void
    {
        foreach ($wrappers as $wrapper) {
            $ship = $wrapper->get();
            $ship->setDockedTo(null);

            if ($ship->isUnderRepair()) {
                $this->addInformation(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getId()));
            }

            $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);

            if ($ship->isTractored()) {
                $this->addLostShip($wrapper, $leadShip, $isFixedFleetMode, $flightRoute, sprintf(_('Die %s wird von einem Traktorstrahl gehalten'), $ship->getName()));
                continue;
            }
            $holdingWeb = $ship->getHoldingWeb();
            if ($holdingWeb !== null) {
                if ($holdingWeb->isFinished()) {
                    $this->addLostShip($wrapper, $leadShip, $isFixedFleetMode, $flightRoute, sprintf(_('Die %s wird von einem Energienetz gehalten'), $ship->getName()));
                    continue;
                } else {
                    $this->tholianWebUtil->releaseShipFromWeb($wrapper);
                }
            }

            $routeMode = $flightRoute->getRouteMode();

            // WA vorhanden? TODO doppelt gemoppelt?
            if (
                $routeMode === RouteModeEnum::ROUTE_MODE_FLIGHT
                && $ship->getSystem() === null && !$ship->isWarpAble()
            ) {
                $this->addLostShip($wrapper, $leadShip, $isFixedFleetMode, $flightRoute, sprintf(_('Die %s verfügt über keinen Warpantrieb'), $ship->getName()));
                continue;
            }

            //WA deaktiveren falls destination in Wurmloch/System
            if (
                $routeMode === RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY
                || $routeMode === RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY
            ) {
                try {
                    $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

                    $this->addInformation(sprintf(_('Die %s deaktiviert den Warpantrieb'), $ship->getName()));
                } catch (ShipSystemException $e) {
                    //nothing to do here
                }
            }

            //Impuls deaktivieren, falls aus wurmloch/system raus
            if (
                $routeMode === RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT
                || $routeMode === RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT
            ) {
                try {
                    $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE);

                    $this->addInformation(sprintf(_('Die %s deaktiviert den Impulsantrieb'), $ship->getName()));
                } catch (ShipSystemException $e) {
                    //nothing to do here
                }
            }

            //Impulsantrieb aktivieren falls innerhalb
            if ($this->shouldActivateImpulsedrive($ship, $routeMode)) {
                try {
                    $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE);

                    $this->addInformation(sprintf(_('Die %s aktiviert den Impulsantrieb'), $ship->getName()));
                } catch (ShipSystemException $e) {
                    $this->addLostShip($wrapper, $leadShip, $isFixedFleetMode, $flightRoute, sprintf(
                        _('Die %s kann den Impulsantrieb nicht aktivieren (%s|%s)'),
                        $ship->getName(),
                        $ship->getPosX(),
                        $ship->getPosY()
                    ));

                    continue;
                }
            }

            //WA aktivieren falls außerhalb
            if ($this->shouldActivateWarpdrive($ship, $routeMode)) {
                try {
                    $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

                    $this->addInformation(sprintf(_('Die %s aktiviert den Warpantrieb'), $ship->getName()));
                } catch (ShipSystemException $e) {
                    $this->addLostShip($wrapper, $leadShip, $isFixedFleetMode, $flightRoute, sprintf(
                        _('Die %s kann den Warpantrieb nicht aktivieren (%s|%s)'),
                        $ship->getName(),
                        $ship->getPosX(),
                        $ship->getPosY()
                    ));

                    continue;
                }
            }

            $tractoredShip = $ship->getTractoringShip();

            if (
                $tractoredShip !== null
                && $tractoredShip->getFleet() !== null
                && $tractoredShip->getFleet()->getShipCount() > 1
            ) {
                $this->deactivateTractorBeam(
                    $wrapper,
                    sprintf(
                        'Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde deaktiviert',
                        $tractoredShip->getName()
                    )
                );
            }
        }
    }

    private function shouldActivateImpulsedrive(ShipInterface $ship, int $routeMode): bool
    {
        if (
            $routeMode === RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY
            || $routeMode === RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY
        ) {
            return true;
        }

        return $routeMode === RouteModeEnum::ROUTE_MODE_FLIGHT
            && $ship->getSystem() !== null && !$ship->getImpulseState();
    }

    private function shouldActivateWarpdrive(ShipInterface $ship, int $routeMode): bool
    {
        if ($routeMode === RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT) {
            return true;
        }

        return $routeMode === RouteModeEnum::ROUTE_MODE_FLIGHT
            && $ship->getSystem() === null && !$ship->getWarpState();
    }

    private function moveOneField(
        ShipInterface $leadShip,
        ShipWrapperInterface $wrapper,
        MapInterface|StarSystemMapInterface $nextWaypoint,
        FlightRouteInterface $flightRoute,
        bool $isFixedFleetMode
    ): void {
        $ship = $wrapper->get();

        // zu wenig Crew
        $buildplan = $ship->getBuildplan();
        if (!$ship->hasEnoughCrew() && $buildplan !== null) {
            $this->addLostShip(
                $wrapper,
                $leadShip,
                $isFixedFleetMode,
                $flightRoute,
                sprintf(
                    _('Es werden %d Crewmitglieder benötigt'),
                    $buildplan->getCrew()
                )
            );
            return;
        }

        $flight_ecost = $ship->getRump()->getFlightEcost();

        $epsSystem = $wrapper->getEpsSystemData();
        $warpdriveSystem = $wrapper->getWarpdriveSystemData();

        //zu wenig E zum weiterfliegen
        if (
            !$ship->getWarpState()
            && ($epsSystem === null || $epsSystem->getEps() < $flight_ecost)
        ) {
            $this->addLostShip(
                $wrapper,
                $leadShip,
                $isFixedFleetMode,
                $flightRoute,
                sprintf(
                    _('Die %s hat nicht genug Energie für den Flug (%d benötigt)'),
                    $ship->getName(),
                    $flight_ecost
                )
            );
            return;
        }

        //zu wenig WarpDriveKapazität zum weiterfliegen
        if (
            $ship->getWarpState()
            && (($warpdriveSystem === null || $warpdriveSystem->getWarpDrive() < 1))
        ) {
            $this->addLostShip(
                $wrapper,
                $leadShip,
                $isFixedFleetMode,
                $flightRoute,
                sprintf(
                    _('Die %s hat nicht genug Warp-Energie für den Flug (1 benötigt)'),
                    $ship->getName()
                )
            );
            return;
        }

        //nächstes Feld nicht passierbar
        $nextFieldType = $nextWaypoint->getFieldType();
        if (!$nextFieldType->getPassable()) {
            $this->addLostShip($wrapper, $leadShip, $isFixedFleetMode, $flightRoute, _('Das nächste Feld kann nicht passiert werden'));
            return;
        }

        $tractoredShip = $ship->getTractoredShip();
        if ($tractoredShip !== null) {
            //can tow tractored ship?
            $abortionMsg = $this->tractorMassPayloadUtil->tryToTow($wrapper, $tractoredShip);

            if ($abortionMsg === null) {
                //Traktorstrahl Kosten
                if (
                    !$ship->getWarpState()
                    && ($epsSystem === null || $epsSystem->getEps() < $tractoredShip->getRump()->getFlightEcost() + 1)
                ) {
                    $this->shipMovementInformationAdder->notEnoughEnergyforTractoring(
                        $ship,
                        $flightRoute->getRouteMode(),
                        $this->informations
                    );
                    $this->deactivateTractorBeam($wrapper, null);
                }
                if (
                    $ship->getWarpState()
                    && ($warpdriveSystem === null || $warpdriveSystem->getWarpDrive() < 2)
                ) {
                    $this->shipMovementInformationAdder->notEnoughEnergyforTractoring(
                        $ship,
                        $flightRoute->getRouteMode(),
                        $this->informations
                    );
                    $this->deactivateTractorBeam($wrapper, null);
                }
            } else {
                $this->deactivateTractorBeam($wrapper, $abortionMsg);
            }
        }

        //MOVE!
        $flightRoute->enterNextWaypoint(
            $ship,
            $nextWaypoint,
            $this->informations
        );

        $this->hasTravelled = true;
        if ($ship === $leadShip) {
            $this->leaderMovedToNextField = true;
        }

        if (!$this->isFleetMode() && $ship->getFleetId()) {
            $this->leaveFleet($wrapper);
        }

        //Flugkosten abziehen
        if ($ship->getWarpState() && $warpdriveSystem !== null) {
            $warpdriveSystem->lowerWarpDrive(1);
        } else if ($epsSystem !== null) {
            $epsSystem->lowerEps($flight_ecost);
        }


        //Traktorstrahl Energie abziehen
        $tractoredShip = $ship->getTractoredShip();
        if ($tractoredShip !== null) {
            if ($ship->getWarpState() && $warpdriveSystem !== null) {
                $warpdriveSystem->lowerWarpDrive(2);
                $flightRoute->enterNextWaypoint(
                    $tractoredShip,
                    $nextWaypoint,
                    $this->informations
                );
            } else if ($epsSystem !== null) {
                $epsSystem->lowerEps($tractoredShip->getRump()->getFlightEcost());
                $flightRoute->enterNextWaypoint(
                    $tractoredShip,
                    $nextWaypoint,
                    $this->informations
                );
            }

            //check for tractor system health
            $tractorSystemSurvived = $this->tractorMassPayloadUtil->tractorSystemSurvivedTowing(
                $wrapper,
                $tractoredShip,
                $this->informations
            );
            if (!$tractorSystemSurvived) {
                $this->deactivateTractorBeam($wrapper, null);
            }

            $this->cancelColonyBlockOrDefend->work($ship, $this->informations, true);
        }

        //Einflugschaden Feldschaden
        if (
            $nextFieldType->getSpecialDamage()
            && (($ship->getSystem() !== null && $nextFieldType->getSpecialDamageInnerSystem())
                || ($ship->getSystem() === null && !$ship->getWarpState() && !$nextFieldType->getSpecialDamageInnerSystem()))
        ) {
            $this->addInformation(sprintf(_('%s in Sektor %d|%d'), $nextFieldType->getName(), $ship->getPosX(), $ship->getPosY()));

            $this->applyFieldDamage($wrapper, $leadShip, $nextFieldType->getSpecialDamage(), true, '', $flightRoute);

            if ($ship->isDestroyed()) {
                return;
            }
        }

        //check for deflector state
        $hasEnoughEnergyForDeflector = $this->hasEnoughEnergeForDeflector($ship, $nextFieldType, $epsSystem);
        $deflectorDestroyed = !$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_DEFLECTOR);

        if ($epsSystem !== null) {
            $epsSystem->update();
        }

        if ($warpdriveSystem !== null) {
            $warpdriveSystem->update();
        }

        //Einflugschaden Energiemangel oder Deflektor zerstört
        if (!$hasEnoughEnergyForDeflector || $deflectorDestroyed) {
            $dmgCause = $deflectorDestroyed ?
                'Deflektor außer Funktion.' :
                'Nicht genug Energie für den Deflektor.';

            $this->applyFieldDamage($wrapper, $leadShip, $nextFieldType->getDamage(), false, $dmgCause, $flightRoute);
        }
    }

    private function hasEnoughEnergeForDeflector(
        ShipInterface $ship,
        MapFieldTypeInterface $nextFieldType,
        ?EpsSystemData $epsSystem
    ): bool {
        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_DEFLECTOR)) {
            return false;
        }

        $energyCost = $nextFieldType->getEnergyCosts();
        if ($energyCost === 0) {
            return true;
        }

        if ($epsSystem === null) {
            return false;
        }

        if ($epsSystem->getEps() < $energyCost) {
            $epsSystem->setEps(0);
            return false;
        }

        $epsSystem->lowerEps($nextFieldType->getEnergyCosts());
        return true;
    }

    private function applyFieldDamage(
        ShipWrapperInterface $wrapper,
        ShipInterface $leadShip,
        int $damage,
        bool $isAbsolutDmg,
        string $cause,
        FlightRouteInterface $flightRoute
    ): void {
        $ship = $wrapper->get();

        //tractored ship
        $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
        if ($tractoredShipWrapper !== null) {
            $tractoredShip = $tractoredShipWrapper->get();
            $dmg = $isAbsolutDmg ? $damage : $tractoredShip->getMaxHull() * $damage / 100;

            $this->addInformation(sprintf(_('%sDie %s wurde in Sektor %d|%d beschädigt'), $cause, $tractoredShip->getName(), $ship->getPosX(), $ship->getPosY()));
            $this->addInformationMerge($this->applyDamage->damage(
                new DamageWrapper((int) ceil($dmg)),
                $tractoredShipWrapper
            )->getInformations());

            if ($tractoredShip->isDestroyed()) {
                $this->entryCreator->addShipEntry(sprintf(
                    _('Die %s (%s) wurde beim Einflug in Sektor %s zerstört'),
                    $tractoredShip->getName(),
                    $tractoredShip->getRump()->getName(),
                    $tractoredShip->getSectorString()
                ));

                $this->shipRemover->destroy($tractoredShipWrapper);
            }
        }

        //ship itself
        $this->addInformation(sprintf(_('%sDie %s wurde in Sektor %d|%d beschädigt'), $cause, $ship->getName(), $ship->getPosX(), $ship->getPosY()));
        $dmg = $isAbsolutDmg ? $damage : $ship->getMaxHull() * $damage / 100;
        $this->addInformationMerge($this->applyDamage->damage(new DamageWrapper((int) ceil($dmg)), $wrapper)->getInformations());

        if ($ship->isDestroyed()) {
            $this->entryCreator->addShipEntry(sprintf(
                _('Die %s (%s) wurde beim Einflug in Sektor %s zerstört'),
                $ship->getName(),
                $ship->getRump()->getName(),
                $ship->getSectorString()
            ));

            $this->shipRemover->destroy($wrapper);
            $this->addLostShip($wrapper, $leadShip, false, $flightRoute, null);
        }
    }

    private function deactivateTractorBeam(ShipWrapperInterface $wrapper, ?string $msg): void
    {
        $this->addInformation($msg);
        unset($this->tractoredShips[$wrapper->get()->getId()]);
        $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true); //active deactivation
    }

    private function addLostShip(
        ShipWrapperInterface $wrapper,
        ShipInterface $leadShip,
        bool $isFixedFleetMode,
        FlightRouteInterface $flightRoute,
        ?string $msg
    ): void {
        if ($msg !== null) {
            $this->addInformation($msg);
        }

        $ship = $wrapper->get();
        $this->lostShips[$ship->getId()] = $ship;

        if ($ship === $leadShip) {
            $flightRoute->abortFlight();
        } elseif (!$isFixedFleetMode) {
            $this->leaveFleet($wrapper, $msg !== null);
        }
    }

    private function leaveFleet(ShipWrapperInterface $wrapper, bool $addLeaveInfo = true): void
    {
        $this->leaveFleet->leaveFleet($wrapper->get());

        if ($addLeaveInfo) {
            $ship = $wrapper->get();
            $this->addInformation(sprintf(_('Die %s hat die Flotte verlassen (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
        }
    }
}
