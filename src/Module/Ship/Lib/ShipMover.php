<?php

namespace Stu\Module\Ship\Lib;

use Stu\Exception\InvalidParamException;
use Stu\Component\Map\MapEnum;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Orm\Entity\FlightSignatureInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;

final class ShipMover implements ShipMoverInterface
{
    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private ShipRepositoryInterface $shipRepository;

    private EntryCreatorInterface $entryCreator;

    private ShipRemoverInterface $shipRemover;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipSystemManagerInterface $shipSystemManager;

    private ApplyDamageInterface $applyDamage;

    private AlertRedHelperInterface $alertRedHelper;

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private AstroEntryRepositoryInterface $astroEntryRepository;

    private int $new_x = 0;
    private int $new_y = 0;
    private int $fleetMode = 0;
    private $fieldData = null;

    private $lostShips = [];

    private $leaderMovedToNextField = false;
    private $hasTravelled = false;

    private $flightSignatures = [];

    public function __construct(
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        ShipRepositoryInterface $shipRepository,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipSystemManagerInterface $shipSystemManager,
        ApplyDamageInterface $applyDamage,
        AlertRedHelperInterface $alertRedHelper,
        FlightSignatureRepositoryInterface $flightSignatureRepository,
        AstroEntryRepositoryInterface $astroEntryRepository
    ) {
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->shipRepository = $shipRepository;
        $this->entryCreator = $entryCreator;
        $this->shipRemover = $shipRemover;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipSystemManager = $shipSystemManager;
        $this->applyDamage = $applyDamage;
        $this->alertRedHelper = $alertRedHelper;
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->astroEntryRepository = $astroEntryRepository;
    }

    private function setDestination(
        ShipInterface $leadShip,
        int $posx,
        int $posy
    ) {
        if ($leadShip->getPosX() != $posx && $leadShip->getPosY() != $posy) {
            new InvalidParamException;
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
            if ($posx > MapEnum::MAP_MAX_X) {
                $posx = MapEnum::MAP_MAX_X;
            }
            if ($posy > MapEnum::MAP_MAX_Y) {
                $posy = MapEnum::MAP_MAX_Y;
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
        ShipInterface $leadShip,
        int $destinationX,
        int $destinationY
    ) {
        //echo "- CAP: ".$leadShip->foobar()."\n";

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

        $ships[] = $leadShip;
        if ($this->isFleetMode()) {
            $ships = array_merge(
                $ships,
                array_filter(
                    $leadShip->getFleet()->getShips()->toArray(),
                    function (ShipInterface $ship) use ($leadShip): bool {
                        return $ship !== $leadShip;
                    }
                )
            );
        }

        $this->getReadyForFlight($leadShip, $ships);

        // fly until destination arrived
        while (!$this->isDestinationArrived($leadShip)) {
            $this->leaderMovedToNextField = false;

            $currentField = $this->getFieldData($leadShip, $leadShip->getPosX(), $leadShip->getPosY());
            $nextField = $this->getNextField($leadShip, $flightMethod);

            // move every ship by one field
            foreach ($ships as $ship) {
                if (
                    !array_key_exists($ship->getId(), $this->lostShips)
                    && ($ship === $leadShip || $this->leaderMovedToNextField)
                ) {
                    $this->moveOneField($leadShip, $ship, $flightMethod, $currentField, $nextField);
                }
            }

            //if moving in warp skip AR check
            if ($leadShip->getWarpState()) {
                continue;
            }

            if (!$this->areShipsLeft($ships)) {
                continue;
            }

            //Alarm-Rot check
            $shipsToShuffle = $this->alertRedHelper->checkForAlertRedShips($leadShip, $this->informations);
            shuffle($shipsToShuffle);
            foreach ($shipsToShuffle as $alertShip) {
                // if there are ships left
                if ($this->areShipsLeft($ships)) {
                    $this->alertRedHelper->performAttackCycle($alertShip, $leadShip, $this->informations);
                } else {
                    break;
                }

                // check for destroyed ships
                foreach ($ships as $ship) {
                    if ($ship->getIsDestroyed()) {
                        $this->addLostShip($ship, $leadShip, null);
                    }
                }
            }
        }

        //skip save and log info if flight did not happen
        if (!$this->hasTravelled) {
            return;
        }

        // save all ships
        foreach ($ships as $ship) {
            if (!$ship->getIsDestroyed()) {
                $this->shipRepository->save($ship);
            }
            if ($ship->isTraktorbeamActive()) {
                $this->addInformation(sprintf(_('Die %s wurde per Traktorstrahl mitgezogen'), $ship->getTraktorShip()->getName()));
                $this->shipRepository->save($ship->getTraktorShip());
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

        $this->saveFlightSignatures();
    }

    private function areShipsLeft(array $ships): bool
    {
        foreach ($ships as $ship) {
            if (!array_key_exists($ship->getId(), $this->lostShips)) {
                return true;
            }
        }

        return false;
    }

    private function getReadyForFlight(ShipInterface $leadShip, array $ships): void
    {
        foreach ($ships as $ship) {
            $ship->setDockedTo(null);
            if ($ship->getState() == ShipStateEnum::SHIP_STATE_REPAIR) {
                $ship->cancelRepair();
                $this->addInformation(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getId()));
            }

            if ($ship->getTraktorMode() == 2) {
                $this->addLostShip($ship, $leadShip, sprintf(_('Die %s wird von einem Traktorstrahl gehalten'), $ship->getName()));
                continue;
            }
            // WA vorhanden?
            if ($ship->getSystem() === null && !$ship->isWarpAble()) {
                $this->addLostShip($ship, $leadShip, sprintf(_('Die %s verfügt über keinen Warpantrieb'), $ship->getName()));
                continue;
            }
            //Impulsantrieb aktivieren falls innerhalb
            if ($ship->getSystem() !== null && !$ship->getImpulseState()) {
                try {
                    $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE);

                    $this->addInformation(sprintf(_('Die %s aktiviert den Impulsantrieb'), $ship->getName()));
                } catch (ShipSystemException $e) {
                    $this->addLostShip($ship, $leadShip, sprintf(
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
                    $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

                    $this->addInformation(sprintf(_('Die %s aktiviert den Warpantrieb'), $ship->getName()));
                } catch (ShipSystemException $e) {
                    $this->addLostShip($ship, $leadShip, sprintf(
                        _('Die %s kann den Warpantrieb nicht aktivieren (%s|%s)'),
                        $ship->getName(),
                        $ship->getPosX(),
                        $ship->getPosY()
                    ));

                    continue;
                }
            }
            if ($ship->getEps() == 0) {
                $this->addLostShip($ship, $leadShip, sprintf(
                    _('Die %s hat nicht genug Energie für den Flug'),
                    $ship->getName()
                ));
                continue;
            }
            if (
                $ship->getTraktorMode() == 1 && $ship->getTraktorShip()->getFleetId()
                && $ship->getTraktorShip()->getFleet()->getShipCount() > 1
            ) {
                $this->deactivateTraktorBeam(
                    $ship,
                    sprintf(
                        _('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde deaktiviert'),
                        $ship->getTraktorShip()->getName()
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
        ShipInterface $ship,
        $flightMethod,
        $currentField,
        $nextField
    ) {
        // zu wenig Crew
        if (!$ship->hasEnoughCrew()) {
            $this->addLostShip(
                $ship,
                $leadShip,
                sprintf(
                    _('Es werden %d Crewmitglieder benötigt'),
                    $ship->getBuildplan()->getCrew()
                )
            );
            return;
        }

        $flight_ecost = $ship->getRump()->getFlightEcost();

        //zu wenig E zum weiterfliegen
        if ($ship->getEps() < $flight_ecost) {
            $this->addLostShip(
                $ship,
                $leadShip,
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
            $this->addLostShip($ship, $leadShip, _('Das nächste Feld kann nicht passiert werden'));
            return;
        }

        //Traktorstrahl Kosten
        if ($ship->isTraktorbeamActive() && $ship->getEps() < $ship->getTraktorShip()->getRump()->getFlightEcost() + 1) {
            $this->deactivateTraktorBeam($ship, sprintf(_('Der Traktorstrahl auf die %s wurde in Sektor %d|%d aufgrund Energiemangels deaktiviert'), $ship->getTraktorShip()->getName(), $ship->getPosX(), $ship->getPosY()));
        }

        //MOVE!
        $met = 'fly' . $flightMethod;
        $this->$met($ship);
        if ($ship->getSystem() === null) {
            $ship->setMap($nextField);
        } else {
            $ship->setStarsystemMap($nextField);
        }
        $this->hasTravelled = true;
        if ($ship === $leadShip) {
            $this->leaderMovedToNextField = true;
        }


        if (!$this->isFleetMode() && $ship->getFleetId()) {
            $this->leaveFleet($ship);
        }
        //Flugkosten abziehen
        $ship->setEps($ship->getEps() - $flight_ecost);

        //Traktorstrahl Energie abziehen
        if ($ship->isTraktorbeamActive()) {
            $ship->setEps($ship->getEps() - $ship->getTraktorShip()->getRump()->getFlightEcost());
            $this->$met($ship->getTraktorShip());
            if ($ship->getSystem() === null) {
                $ship->getTraktorShip()->setMap($nextField);
            } else {
                $ship->getTraktorShip()->setStarsystemMap($nextField);
            }

            //check astro stuff for tractor
            if ($ship->getSystem() !== null) {
                $this->checkAstronomicalStuff($ship->getTraktorShip(), $nextField);
            }
        }

        //create flight signatures
        $this->addFlightSignatures($ship, $flightMethod, $currentField, $nextField, $leadShip->getSystem() !== null);

        //check astro stuff
        if ($ship->getSystem() !== null) {
            $this->checkAstronomicalStuff($ship, $nextField);
        }

        //Einflugschaden Feldschaden
        if ($nextField->getFieldType()->getSpecialDamage() && (($ship->getSystem() !== null && $nextField->getFieldType()->getSpecialDamageInnerSystem()) || ($ship->getSystem() === null && !$ship->getWarpState() && !$nextField->getFieldType()->getSpecialDamageInnerSystem()))) {
            $this->addInformation(sprintf(_('%s in Sektor %d|%d'), $nextField->getFieldType()->getName(), $ship->getPosX(), $ship->getPosY()));

            $this->applyFieldDamage($ship, $leadShip, $nextField->getFieldType()->getSpecialDamage(), true, '');

            if ($ship->getIsDestroyed()) {
                return;
            }
        }

        //check for deflector state
        $notEnoughEnergyForDeflector = false;
        $deflectorDestroyed = false;
        if ($ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_DEFLECTOR)) {
            $notEnoughEnergyForDeflector = $nextField->getFieldType()->getEnergyCosts() > $ship->getEps();

            if ($notEnoughEnergyForDeflector) {
                $ship->setEps(0);
            } else {
                $ship->setEps($ship->getEps() - $nextField->getFieldType()->getEnergyCosts());
            }
        } else {
            $deflectorDestroyed = true;
        }

        //Einflugschaden Energiemangel oder Deflektor zerstört
        if ($notEnoughEnergyForDeflector || $deflectorDestroyed) {
            $dmgCause = $notEnoughEnergyForDeflector ?
                'Nicht genug Energie für den Deflektor.' :
                'Deflektor außer Funktion.';

            $this->applyFieldDamage($ship, $leadShip, $nextField->getFieldType()->getDamage(), false, $dmgCause);
        }
    }

    private function applyFieldDamage($ship, $leadShip, $damage, $isAbsolutDmg, $cause): void
    {
        //tractored ship
        if ($ship->isTraktorbeamActive()) {
            $tractorShip = $ship->getTraktorShip();
            $dmg = $isAbsolutDmg ? $damage : $tractorShip->getMaxHuell() * $damage / 100;

            $this->addInformation(sprintf(_('%sDie %s wurde in Sektor %d|%d beschädigt'), $cause, $tractorShip->getName(), $ship->getPosX(), $ship->getPosY()));
            $damageMsg = $this->applyDamage->damage(new DamageWrapper((int) ceil($dmg)), $tractorShip);
            $this->addInformationMerge($damageMsg);

            if ($tractorShip->getIsDestroyed()) {
                $this->entryCreator->addShipEntry(sprintf(_('Die %s wurde beim Einflug in Sektor %s zerstört'), $tractorShip->getName(), $tractorShip->getSectorString()));

                $this->shipRemover->destroy($tractorShip);
            }
        }

        //ship itself
        $this->addInformation(sprintf(_('%sDie %s wurde in Sektor %d|%d beschädigt'), $cause, $ship->getName(), $ship->getPosX(), $ship->getPosY()));
        $dmg = $isAbsolutDmg ? $damage : $ship->getMaxHuell() * $damage / 100;
        $damageMsg = $this->applyDamage->damage(new DamageWrapper((int) ceil($dmg)), $ship);
        $this->addInformationMerge($damageMsg);

        if ($ship->getIsDestroyed()) {
            $this->entryCreator->addShipEntry(sprintf(_('Die %s wurde beim Einflug in Sektor %s zerstört'), $ship->getName(), $ship->getSectorString()));

            $this->shipRemover->destroy($ship);
            $this->addLostShip($ship, $leadShip, null);
        }
    }

    private function deactivateTraktorBeam(ShipInterface $ship, string $msg)
    {
        $this->addInformation($msg);
        $this->privateMessageSender->send(
            (int) $ship->getUser()->getId(),
            (int) $ship->getTraktorShip()->getUser()->getId(),
            sprintf(_('Der auf die %s gerichtete Traktorstrahl wurde in Sektor %s deaktiviert'), $ship->getTraktorShip()->getName(), $ship->getSectorString()),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );
        $ship->deactivateTraktorBeam();
    }

    private function addLostShip(ShipInterface $ship, ShipInterface $leadShip, ?string $msg)
    {
        if ($msg !== null) {
            $this->addInformation($msg);
        }

        $this->lostShips[$ship->getId()] = $ship;

        if ($ship === $leadShip) {
            $this->updateDestination($ship->getPosX(), $ship->getPosY());
        } else {
            $this->leaveFleet($ship, $msg !== null);
        }
    }

    private function leaveFleet(ShipInterface $ship, bool $addLeaveInfo = true)
    {
        $ship->leaveFleet();

        if ($addLeaveInfo) {

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

    private function addFlightSignatures($ship, $flightMethod, $currentField, $nextField, bool $isSystem): void
    {
        $fromSignature = $this->createSignature($ship, $currentField, $isSystem);
        $toSignature = $this->createSignature($ship, $nextField, $isSystem);

        switch ($flightMethod) {
            case ShipEnum::DIRECTION_RIGHT:
                $fromSignature->setToDirection(ShipEnum::DIRECTION_RIGHT);
                $toSignature->setFromDirection(ShipEnum::DIRECTION_LEFT);
                break;
            case ShipEnum::DIRECTION_LEFT:
                $fromSignature->setToDirection(ShipEnum::DIRECTION_LEFT);
                $toSignature->setFromDirection(ShipEnum::DIRECTION_RIGHT);
                break;
            case ShipEnum::DIRECTION_TOP:
                $fromSignature->setToDirection(ShipEnum::DIRECTION_TOP);
                $toSignature->setFromDirection(ShipEnum::DIRECTION_BOTTOM);
                break;
            case ShipEnum::DIRECTION_BOTTOM:
                $fromSignature->setToDirection(ShipEnum::DIRECTION_BOTTOM);
                $toSignature->setFromDirection(ShipEnum::DIRECTION_TOP);
                break;
        }

        $this->flightSignatures[] = $fromSignature;
        $this->flightSignatures[] = $toSignature;
    }

    private function checkAstronomicalStuff(ShipInterface $ship, StarSystemMapInterface $nextField): void
    {
        if (!$ship->getAstroState()) {
            return;
        }

        $astroEntry = $this->astroEntryRepository->getByUserAndSystem($ship->getUser(), $ship->getSystemsId());

        if ($astroEntry === null) {
            return;
        }

        if ($astroEntry->getState() == AstronomicalMappingEnum::PLANNED) {
            if ($astroEntry->getStarsystemMap1() === $nextField) {
                $astroEntry->setStarsystemMap1(null);
                $this->addInformation(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
            } else if ($astroEntry->getStarsystemMap2() === $nextField) {
                $astroEntry->setStarsystemMap2(null);
                $this->addInformation(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
            } else if ($astroEntry->getStarsystemMap3() === $nextField) {
                $astroEntry->setStarsystemMap3(null);
                $this->addInformation(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
            } else if ($astroEntry->getStarsystemMap4() === $nextField) {
                $astroEntry->setStarsystemMap4(null);
                $this->addInformation(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
            } else if ($astroEntry->getStarsystemMap5() === $nextField) {
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

    private function createSignature($ship, $field, bool $isSystem): FlightSignatureInterface
    {
        $signature = $this->flightSignatureRepository->prototype();

        $signature->setUser($ship->getUser());
        $signature->setShip($ship);
        $signature->setShipName($ship->getName());
        $signature->setRump($ship->getRump());
        $signature->setIsCloaked($ship->getCloakState());
        $signature->setTime(time());
        if ($isSystem) {
            $signature->setStarsystemMap($field);
        } else {
            $signature->setMap($field);
        }

        return $signature;
    }

    private function saveFlightSignatures(): void
    {
        $this->flightSignatureRepository->saveAll($this->flightSignatures);
    }
}
