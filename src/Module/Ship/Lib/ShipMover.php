<?php

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Exception\SanityCheckException;
use Stu\Lib\DamageWrapper;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Movement\ShipMovementComponentsFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShipMover implements ShipMoverInterface
{
    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private ShipRepositoryInterface $shipRepository;

    private EntryCreatorInterface $entryCreator;

    private ShipRemoverInterface $shipRemover;

    private ShipSystemManagerInterface $shipSystemManager;

    private ApplyDamageInterface $applyDamage;

    private AlertRedHelperInterface $alertRedHelper;

    private AstroEntryRepositoryInterface $astroEntryRepository;

    private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend;

    private TractorMassPayloadUtilInterface $tractorMassPayloadUtil;

    private ShipStateChangerInterface $shipStateChanger;

    private TholianWebUtilInterface $tholianWebUtil;

    private ShipMovementComponentsFactoryInterface $shipMovementComponentsFactory;

    private int $new_x = 0;
    private int $new_y = 0;
    private int $fleetMode = 0;
    private $fieldData = null;

    private $lostShips = [];
    private $tractoredShips = [];

    private $leaderMovedToNextField = false;
    private $hasTravelled = false;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        ShipRepositoryInterface $shipRepository,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover,
        ShipSystemManagerInterface $shipSystemManager,
        ApplyDamageInterface $applyDamage,
        AlertRedHelperInterface $alertRedHelper,
        AstroEntryRepositoryInterface $astroEntryRepository,
        CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend,
        TractorMassPayloadUtilInterface  $tractorMassPayloadUtil,
        ShipStateChangerInterface $shipStateChanger,
        TholianWebUtilInterface $tholianWebUtil,
        ShipMovementComponentsFactoryInterface $shipMovementComponentsFactory
    ) {
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->shipRepository = $shipRepository;
        $this->entryCreator = $entryCreator;
        $this->shipRemover = $shipRemover;
        $this->shipSystemManager = $shipSystemManager;
        $this->applyDamage = $applyDamage;
        $this->alertRedHelper = $alertRedHelper;
        $this->astroEntryRepository = $astroEntryRepository;
        $this->cancelColonyBlockOrDefend = $cancelColonyBlockOrDefend;
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
        $this->shipStateChanger = $shipStateChanger;
        $this->tholianWebUtil = $tholianWebUtil;
        $this->shipMovementComponentsFactory = $shipMovementComponentsFactory;
    }

    private function setDestination(
        ShipInterface $leadShip,
        int $posx,
        int $posy
    ) {
        if ($leadShip->getPosX() != $posx && $leadShip->getPosY() != $posy) {
            throw new SanityCheckException(
                sprintf(
                    'userId %d tried to navigate from %d|%d to invalid position %d|%d',
                    $leadShip->getUser()->getId(),
                    $leadShip->getPosX(),
                    $leadShip->getPosY(),
                    $posx,
                    $posy
                )
            );
        }
        if ($posx < 1) {
            $posx = 1;
        }
        if ($posy < 1) {
            $posy = 1;
        }
        if ($leadShip->getSystem() !== null) {
            $sys = $leadShip->getSystem();
            if ($posx > $sys->getMaxX()) {
                $posx = $sys->getMaxX();
            }
            if ($posy > $sys->getMaxY()) {
                $posy = $sys->getMaxY();
            }
        } else {
            $layer = $leadShip->getLayer();

            if ($posx > $layer->getWidth()) {
                $posx = $layer->getWidth();
            }
            if ($posy > $layer->getHeight()) {
                $posy = $layer->getHeight();
            }
        }
        $this->setDestX($posx);
        $this->setDestY($posy);
    }

    /**
     * sets member fleetMode = 1 if ship is in fleet and fleet leader
     */
    private function determineFleetMode(ShipInterface $leadShip): void
    {
        if ($leadShip->getFleet() === null) {
            return;
        }
        if (!$leadShip->isFleetLeader()) {
            return;
        }
        $this->setFleetMode(1);
    }

    private function setFleetMode($value)
    {
        $this->fleetMode = $value;
    }

    private function isFleetMode()
    {
        return $this->fleetMode;
    }

    private function getDestX()
    {
        return $this->new_x;
    }

    private function getDestY()
    {
        return $this->new_y;
    }

    private function setDestX($value)
    {
        $this->new_x = $value;
    }

    private function setDestY($value)
    {
        $this->new_y = $value;
    }

    private array $informations = [];

    private function addInformation($value)
    {
        $this->informations[] = $value;
    }

    private function addInformationMerge($value)
    {
        if (!is_array($value)) {
            return;
        }
        $this->informations = array_merge($this->getInformations(), $value);
    }

    public function getInformations(): array
    {
        return $this->informations;
    }

    public function checkAndMove(
        ShipWrapperInterface $leadShipWrapper,
        int $destinationX,
        int $destinationY
    ) {
        //echo "- CAP: ".$leadShip->foobar()."\n";

        $leadShip = $leadShipWrapper->get();
        if ($leadShip->isFleetLeader() && $leadShip->getFleet()->getDefendedColony() !== null) {
            $this->addInformation(_('Flug während Kolonie-Verteidigung nicht möglich'));
            return;
        }

        if ($leadShip->isFleetLeader() && $leadShip->getFleet()->getBlockedColony() !== null) {
            $this->addInformation(_('Flug während Kolonie-Blockierung nicht möglich'));
            return;
        }

        $this->setDestination($leadShip, $destinationX, $destinationY);
        $this->determineFleetMode($leadShip);
        $flightMethod = $this->determineFlightMethod($leadShip);

        $wrappers = $this->isFleetMode() ? $this->alertRedHelper->getShips($leadShip) : [$leadShipWrapper];

        $isFixedFleetMode = $this->isFleetMode() && $leadShip->getFleet()->isFleetFixed();
        $this->getReadyForFlight($leadShip, $wrappers, $isFixedFleetMode);
        if (!empty($this->lostShips)) {
            $this->addInformation(_('Der Weiterflug wurde abgebrochen!'));
            return;
        }

        $this->initTractoredShips($wrappers);

        // fly until destination arrived
        while (!$this->isDestinationArrived($leadShip)) {
            $this->leaderMovedToNextField = false;

            $currentField = $this->getFieldData($leadShip, $leadShip->getPosX(), $leadShip->getPosY());
            $nextField = $this->getNextField($leadShip, $flightMethod);

            if ($isFixedFleetMode) {
                $reasons = $this->shipMovementComponentsFactory->createShipMovementBlockingDeterminator()->determine($wrappers);

                if (!empty($reasons)) {
                    $this->updateDestination($leadShip->getPosX(), $leadShip->getPosY());
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
                    $this->moveOneField($leadShip, $wrapper, $flightMethod, $currentField, $nextField, $isFixedFleetMode);
                }
            }

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
                        $this->addLostShip($wrapper, $leadShip, false, null);
                    }
                }
            }

            //AR Check for tractored ships
            foreach ($this->tractoredShips as [$tractoringShip, $tractoredShip]) {
                if (!$tractoredShip->isDestroyed()) {
                    $this->informations = array_merge(
                        $this->informations,
                        $this->alertRedHelper->doItAll($tractoredShip, null, $tractoringShip)
                    );
                }
            }
        }

        //skip save and log info if flight did not happen
        if (!$this->hasTravelled) {
            return;
        }

        // save all ships
        foreach ($wrappers as $wrapper) {
            $ship = $wrapper->get();
            if (!$ship->isDestroyed()) {
                $this->shipRepository->save($ship);
            }
            if ($ship->isTractoring()) {
                $this->addInformation(sprintf(_('Die %s wurde per Traktorstrahl mitgezogen'), $ship->getTractoredShip()->getName()));
                $this->shipRepository->save($ship->getTractoredShip());
            }
        }

        if ($this->isFleetMode()) {
            $this->addInformation(
                sprintf(
                    _('Die Flotte fliegt in Sektor %d|%d ein'),
                    $this->getDestX(),
                    $this->getDestY()
                )
            );
        } else {
            $this->addInformation(sprintf(_('Die %s fliegt in Sektor %d|%d ein'), $leadShip->getName(), $leadShip->getPosX(), $leadShip->getPosY()));
        }
    }

    /**
     * @param ShipWrapperInterface[] $wrappers
     */
    private function initTractoredShips(array $wrappers): void
    {
        foreach ($wrappers as $fleetShipWrapper) {
            $fleetShip = $fleetShipWrapper->get();
            if (
                $fleetShip->isTractoring() &&
                !array_key_exists($fleetShip->getId(), $this->lostShips)
            ) {
                $this->tractoredShips[$fleetShip->getId()] = [$fleetShip, $fleetShip->getTractoredShip()];
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
    private function getReadyForFlight(ShipInterface $leadShip, array $wrappers, bool $isFixedFleetMode): void
    {
        foreach ($wrappers as $wrapper) {
            $ship = $wrapper->get();
            $ship->setDockedTo(null);

            if ($ship->isUnderRepair()) {
                $this->addInformation(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getId()));
            }

            $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);

            if ($ship->isTractored()) {
                $this->addLostShip($wrapper, $leadShip, $isFixedFleetMode, sprintf(_('Die %s wird von einem Traktorstrahl gehalten'), $ship->getName()));
                continue;
            }
            $holdingWeb = $ship->getHoldingWeb();
            if ($holdingWeb !== null) {
                if ($holdingWeb->isFinished()) {
                    $this->addLostShip($wrapper, $leadShip, $isFixedFleetMode, sprintf(_('Die %s wird von einem Energienetz gehalten'), $ship->getName()));
                    continue;
                } else {
                    $this->tholianWebUtil->releaseShipFromWeb($wrapper);
                }
            }
            // WA vorhanden?
            if ($ship->getSystem() === null && !$ship->isWarpAble()) {
                $this->addLostShip($wrapper, $leadShip, $isFixedFleetMode, sprintf(_('Die %s verfügt über keinen Warpantrieb'), $ship->getName()));
                continue;
            }

            //Impulsantrieb aktivieren falls innerhalb
            if ($ship->getSystem() !== null && !$ship->getImpulseState()) {
                try {
                    $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE);

                    $this->addInformation(sprintf(_('Die %s aktiviert den Impulsantrieb'), $ship->getName()));
                } catch (ShipSystemException $e) {
                    $this->addLostShip($wrapper, $leadShip, $isFixedFleetMode, sprintf(
                        _('Die %s kann den Impulsantrieb nicht aktivieren (%s|%s)'),
                        $ship->getName(),
                        $ship->getPosX(),
                        $ship->getPosY()
                    ));

                    continue;
                }
            }
            //WA aktivieren falls außerhalb
            if ($ship->getSystem() === null && !$ship->getWarpState()) {
                try {
                    $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

                    $this->addInformation(sprintf(_('Die %s aktiviert den Warpantrieb'), $ship->getName()));
                } catch (ShipSystemException $e) {
                    $this->addLostShip($wrapper, $leadShip, $isFixedFleetMode, sprintf(
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

    private function isDestinationArrived(ShipInterface $ship): bool
    {
        return $this->getDestX() == $ship->getPosX() && $this->getDestY() == $ship->getPosY();
    }

    private function determineFlightMethod(ShipInterface $ship)
    {
        if ($this->getDestX() == $ship->getPosX()) {
            $oldy = $ship->getPosY();
            if ($this->getDestY() > $oldy) {
                return ShipEnum::DIRECTION_BOTTOM;
            } else {
                return ShipEnum::DIRECTION_TOP;
            }
        }
        if ($this->getDestY() == $ship->getPosY()) {
            $oldx = $ship->getPosX();
            if ($this->getDestX() > $oldx) {
                return ShipEnum::DIRECTION_RIGHT;
            } else {
                return ShipEnum::DIRECTION_LEFT;
            }
        }
    }

    private function moveOneField(
        ShipInterface $leadShip,
        ShipWrapperInterface $wrapper,
        $flightMethod,
        $currentField,
        $nextField,
        bool $isFixedFleetMode
    ) {
        $ship = $wrapper->get();

        // zu wenig Crew
        if (!$ship->hasEnoughCrew()) {
            $this->addLostShip(
                $wrapper,
                $leadShip,
                $isFixedFleetMode,
                sprintf(
                    _('Es werden %d Crewmitglieder benötigt'),
                    $ship->getBuildplan()->getCrew()
                )
            );
            return;
        }

        $flight_ecost = $ship->getRump()->getFlightEcost();

        $epsSystem = $wrapper->getEpsSystemData();

        //zu wenig E zum weiterfliegen
        if ($epsSystem === null || $epsSystem->getEps() < $flight_ecost) {
            $this->addLostShip(
                $wrapper,
                $leadShip,
                $isFixedFleetMode,
                sprintf(
                    _('Die %s hat nicht genug Energie für den Flug (%d benötigt)'),
                    $ship->getName(),
                    $flight_ecost
                )
            );
            return;
        }

        //nächstes Feld nicht passierbar
        if (!$nextField->getFieldType()->getPassable()) {
            $this->addLostShip($wrapper, $leadShip, $isFixedFleetMode, _('Das nächste Feld kann nicht passiert werden'));
            return;
        }


        if ($ship->isTractoring()) {
            $tractoredShip = $ship->getTractoredShip();

            //can tow tractored ship?
            $abortionMsg = $this->tractorMassPayloadUtil->tryToTow($wrapper, $tractoredShip);

            if ($abortionMsg === null) {
                //Traktorstrahl Kosten
                if ($epsSystem->getEps() < $tractoredShip->getRump()->getFlightEcost() + 1) {
                    $this->deactivateTractorBeam($wrapper, sprintf(
                        _('Der Traktorstrahl auf die %s wurde in Sektor %d|%d aufgrund Energiemangels deaktiviert'),
                        $tractoredShip->getName(),
                        $ship->getPosX(),
                        $ship->getPosY()
                    ));
                }
            } else {
                $this->deactivateTractorBeam($wrapper, $abortionMsg);
            }
        }

        //MOVE!
        $met = 'fly' . $flightMethod;
        $this->$met($ship);
        if ($ship->getSystem() === null) {
            $ship->updateLocation($nextField, null);
        } else {
            $ship->updateLocation(null, $nextField);
        }
        $this->hasTravelled = true;
        if ($ship === $leadShip) {
            $this->leaderMovedToNextField = true;
        }


        if (!$this->isFleetMode() && $ship->getFleetId()) {
            $this->leaveFleet($wrapper);
        }
        //Flugkosten abziehen
        $epsSystem->setEps($epsSystem->getEps() - $flight_ecost);

        //Traktorstrahl Energie abziehen
        if ($ship->isTractoring()) {
            $tractoredShip = $ship->getTractoredShip();
            $epsSystem->setEps($epsSystem->getEps() - $tractoredShip->getRump()->getFlightEcost());
            $this->$met($tractoredShip);
            if ($ship->getSystem() === null) {
                $tractoredShip->updateLocation($nextField, null);
            } else {
                $tractoredShip->updateLocation(null, $nextField);
            }

            //check for tractor system health
            $msg = [];
            $tractorSystemSurvived = $this->tractorMassPayloadUtil->tractorSystemSurvivedTowing($wrapper, $tractoredShip, $msg);
            if (!$tractorSystemSurvived) {
                $this->deactivateTractorBeam($wrapper, current($msg));
            } else {
                $this->addInformationMerge($msg);
            }

            //check astro stuff for tractor
            if ($ship->getSystem() !== null) {
                $this->checkAstronomicalStuff($tractoredShip, $nextField);
            }
            $this->addInformationMerge($this->cancelColonyBlockOrDefend->work($ship, true));
        }

        //create flight signatures
        $flightSignatureCreator = $this->shipMovementComponentsFactory->createFlightSignatureCreator();
        if ($leadShip->getSystem() !== null) {
            $this->shipMovementComponentsFactory->createFlightSignatureCreator()->createInnerSystemSignatures(
                $ship,
                $flightMethod,
                $currentField,
                $nextField,
            );
        } else {
            $this->shipMovementComponentsFactory->createFlightSignatureCreator()->createOuterSystemSignatures(
                $ship,
                $flightMethod,
                $currentField,
                $nextField,
            );
        }


        //check astro stuff
        if ($ship->getSystem() !== null) {
            $this->checkAstronomicalStuff($ship, $nextField);
        }

        //Einflugschaden Feldschaden
        if (
            $nextField->getFieldType()->getSpecialDamage()
            && (($ship->getSystem() !== null && $nextField->getFieldType()->getSpecialDamageInnerSystem())
                || ($ship->getSystem() === null && !$ship->getWarpState() && !$nextField->getFieldType()->getSpecialDamageInnerSystem()))
        ) {
            $this->addInformation(sprintf(_('%s in Sektor %d|%d'), $nextField->getFieldType()->getName(), $ship->getPosX(), $ship->getPosY()));

            $this->applyFieldDamage($wrapper, $leadShip, $nextField->getFieldType()->getSpecialDamage(), true, '');

            if ($ship->isDestroyed()) {
                return;
            }
        }

        //check for deflector state
        $notEnoughEnergyForDeflector = false;
        $deflectorDestroyed = false;
        if ($ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_DEFLECTOR)) {
            $notEnoughEnergyForDeflector = $nextField->getFieldType()->getEnergyCosts() > $epsSystem->getEps();

            if ($notEnoughEnergyForDeflector) {
                $epsSystem->setEps(0);
            } else {
                $epsSystem->setEps($epsSystem->getEps() - $nextField->getFieldType()->getEnergyCosts());
            }
        } else {
            $deflectorDestroyed = true;
        }

        $epsSystem->update();

        //Einflugschaden Energiemangel oder Deflektor zerstört
        if ($notEnoughEnergyForDeflector || $deflectorDestroyed) {
            $dmgCause = $notEnoughEnergyForDeflector ?
                'Nicht genug Energie für den Deflektor.' :
                'Deflektor außer Funktion.';

            $this->applyFieldDamage($wrapper, $leadShip, $nextField->getFieldType()->getDamage(), false, $dmgCause);
        }
    }

    private function applyFieldDamage(ShipWrapperInterface $wrapper, ShipInterface $leadShip, $damage, $isAbsolutDmg, $cause): void
    {
        $ship = $wrapper->get();

        //tractored ship
        if ($ship->isTractoring()) {
            $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
            $tractoredShip = $ship->getTractoredShip();
            $dmg = $isAbsolutDmg ? $damage : $tractoredShip->getMaxHull() * $damage / 100;

            $this->addInformation(sprintf(_('%sDie %s wurde in Sektor %d|%d beschädigt'), $cause, $tractoredShip->getName(), $ship->getPosX(), $ship->getPosY()));
            $damageMsg = $this->applyDamage->damage(
                new DamageWrapper((int) ceil($dmg)),
                $tractoredShipWrapper
            );
            $this->addInformationMerge($damageMsg);

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
        $damageMsg = $this->applyDamage->damage(new DamageWrapper((int) ceil($dmg)), $wrapper);
        $this->addInformationMerge($damageMsg);

        if ($ship->isDestroyed()) {
            $this->entryCreator->addShipEntry(sprintf(
                _('Die %s (%s) wurde beim Einflug in Sektor %s zerstört'),
                $ship->getName(),
                $ship->getRump()->getName(),
                $ship->getSectorString()
            ));

            $this->shipRemover->destroy($wrapper);
            $this->addLostShip($wrapper, $leadShip, false, null);
        }
    }

    private function deactivateTractorBeam(ShipWrapperInterface $wrapper, string $msg)
    {
        $this->addInformation($msg);
        unset($this->tractoredShips[$wrapper->get()->getId()]);
        $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true); //active deactivation
    }

    private function addLostShip(ShipWrapperInterface $wrapper, ShipInterface $leadShip, bool $isFixedFleetMode, ?string $msg)
    {
        if ($msg !== null) {
            $this->addInformation($msg);
        }

        $ship = $wrapper->get();
        $this->lostShips[$ship->getId()] = $ship;

        if ($ship === $leadShip) {
            $this->updateDestination($ship->getPosX(), $ship->getPosY());
        } elseif (!$isFixedFleetMode) {
            $this->leaveFleet($wrapper, $msg !== null);
        }
    }

    private function leaveFleet(ShipWrapperInterface $wrapper, bool $addLeaveInfo = true)
    {
        $wrapper->leaveFleet();

        if ($addLeaveInfo) {
            $ship = $wrapper->get();
            $this->addInformation(sprintf(_('Die %s hat die Flotte verlassen (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
        }
    }

    private function getNextField(ShipInterface $leadShip, $flightMethod)
    {
        switch ($flightMethod) {
            case ShipEnum::DIRECTION_RIGHT:
                return $this->getFieldData($leadShip, $leadShip->getPosX() + 1, $leadShip->getPosY());
            case ShipEnum::DIRECTION_LEFT:
                return $this->getFieldData($leadShip, $leadShip->getPosX() - 1, $leadShip->getPosY());
            case ShipEnum::DIRECTION_TOP:
                return $this->getFieldData($leadShip, $leadShip->getPosX(), $leadShip->getPosY() - 1);
            case ShipEnum::DIRECTION_BOTTOM:
                return $this->getFieldData($leadShip, $leadShip->getPosX(), $leadShip->getPosY() + 1);
        }
    }


    private function updateDestination(&$posx, &$posy)
    {
        $this->setDestX($posx);
        $this->setDestY($posy);
    }

    //down
    private function fly2(ShipInterface $ship)
    {
        $ship->setFlightDirection(ShipEnum::DIRECTION_BOTTOM);
    }

    //up
    private function fly4(ShipInterface $ship)
    {
        $ship->setFlightDirection(ShipEnum::DIRECTION_TOP);
    }

    //right
    private function fly3(ShipInterface $ship)
    {
        $ship->setFlightDirection(ShipEnum::DIRECTION_RIGHT);
    }

    //left
    private function fly1(ShipInterface $ship)
    {
        $ship->setFlightDirection(ShipEnum::DIRECTION_LEFT);
    }

    private function getFieldData(ShipInterface $leadShip, $x, $y)
    {
        if ($this->fieldData === null) {
            $sx = (int) $leadShip->getPosX();
            $sy = (int) $leadShip->getPosY();
            $destx = (int) $this->getDestX();
            $desty = (int) $this->getDestY();

            if ($sy > $desty) {
                $oy = $sy;
                $sy = $desty;
                $desty = $oy;
            }
            if ($sx > $destx) {
                $ox = $sx;
                $sx = $destx;
                $destx = $ox;
            }
            if ($leadShip->getSystem() === null) {
                $result = $this->mapRepository->getByCoordinateRange(
                    $leadShip->getLayerId(),
                    $sx,
                    $destx,
                    $sy,
                    $desty
                );

                foreach ($result as $field) {
                    $this->fieldData[sprintf('%d_%d', $field->getCx(), $field->getCy())] = $field;
                }
            } else {
                $result = $this->starSystemMapRepository->getByCoordinateRange(
                    $leadShip->getSystem(),
                    $sx,
                    $destx,
                    $sy,
                    $desty
                );

                foreach ($result as $field) {
                    $this->fieldData[sprintf('%d_%d', $field->getSx(), $field->getSy())] = $field;
                }
            }
        }
        return $this->fieldData[$x . "_" . $y];
    }

    private function checkAstronomicalStuff(ShipInterface $ship, StarSystemMapInterface $nextField): void
    {
        if (!$ship->getAstroState()) {
            return;
        }

        $astroEntry = $this->astroEntryRepository->getByUserAndSystem($ship->getUser()->getId(), $ship->getSystemsId());

        if ($astroEntry === null) {
            return;
        }

        if ($astroEntry->getState() == AstronomicalMappingEnum::PLANNED) {
            if ($astroEntry->getStarsystemMap1() === $nextField) {
                $astroEntry->setStarsystemMap1(null);
                $this->addInformation(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
            } elseif ($astroEntry->getStarsystemMap2() === $nextField) {
                $astroEntry->setStarsystemMap2(null);
                $this->addInformation(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
            } elseif ($astroEntry->getStarsystemMap3() === $nextField) {
                $astroEntry->setStarsystemMap3(null);
                $this->addInformation(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
            } elseif ($astroEntry->getStarsystemMap4() === $nextField) {
                $astroEntry->setStarsystemMap4(null);
                $this->addInformation(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
            } elseif ($astroEntry->getStarsystemMap5() === $nextField) {
                $astroEntry->setStarsystemMap5(null);
                $this->addInformation(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
            }

            if ($astroEntry->isMeasured()) {
                $astroEntry->setState(AstronomicalMappingEnum::MEASURED);
                $this->addInformation(sprintf(_('Die %s hat alle Kartographierungs-Messpunkte erreicht'), $ship->getName()));
            }
        }

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING && $astroEntry->getState() === AstronomicalMappingEnum::FINISHING) {
            $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
            $ship->setAstroStartTurn(null);
            $astroEntry->setState(AstronomicalMappingEnum::MEASURED);
            $astroEntry->setAstroStartTurn(null);
            $this->addInformation(sprintf(_('Die %s hat die Kartographierungs-Finalisierung abgebrochen'), $ship->getName()));
        }

        $this->astroEntryRepository->save($astroEntry);
    }
}
