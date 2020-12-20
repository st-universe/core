<?php

namespace Stu\Module\Ship\Lib;

use Stu\Exception\InvalidParamException;
use Stu\Component\Map\MapEnum;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShipMoverV2 implements ShipMoverV2Interface
{
    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private ShipRepositoryInterface $shipRepository;

    private EntryCreatorInterface $entryCreator;

    private ShipRemoverInterface $shipRemover;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipSystemManagerInterface $shipSystemManager;

    private ApplyDamageInterface $applyDamage;

    private int $new_x = 0;
    private int $new_y = 0;
    private int $fleetMode = 0;
    private $fieldData = null;
    private int $flightFields = 0;

    private $lostShips = [];

    public function __construct(
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        ShipRepositoryInterface $shipRepository,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipSystemManagerInterface $shipSystemManager,
        ApplyDamageInterface $applyDamage
    ) {
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->shipRepository = $shipRepository;
        $this->entryCreator = $entryCreator;
        $this->shipRemover = $shipRemover;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipSystemManager = $shipSystemManager;
        $this->applyDamage = $applyDamage;
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

    private function getFieldCount(ShipInterface $ship): int
    {
        if ($ship->getPosX() == $this->getDestX()) {
            $fields = abs($ship->getPosY() - $this->getDestY());
        } else {
            $fields = abs($ship->getPosX() - $this->getDestX());
        }
        $energyCosts = $ship->getRump()->getFlightEcost();

        if ($fields * $energyCosts > $ship->getEps()) {
            $fields = (int)floor($ship->getEps() / $energyCosts);
        }
        return $fields;
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
        $this->setDestination($leadShip, $destinationX, $destinationY);
        $this->determineFleetMode($leadShip);
        $flightMethod = $this->determineFlightMethod($leadShip);

        $ships[] = $leadShip;
        $msg = [];
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

        //TODO wird das nicht eh für jedes schiff geprüft?!?!
        if ($this->isFleetMode()) {
            if ($leadShip->getEps() == 0) {
                $this->addInformation(
                    sprintf(_('Die %s hat nicht genug Energie für den Flug'),
                    $leadShip->getName())
                );
                return;
            }
            if ($leadShip->getBuildplan()->getCrew() > 0 && $leadShip->getCrewCount() == 0) {
                $this->addInformation(
                    sprintf(_('Es werden %d Crewmitglieder benötigt'),
                    $leadShip->getBuildplan()->getCrew())
                );
                return;
            }
        }
        // fly until destination arrived
        while (!$this->isDestinationArrived($leadShip)) {
            $nextfield = $this->getNextField($leadShip, $flightMethod);

            // move every ship by one field
            foreach ($ships as $ship) {
                if (array_key_exists($ship->getId(), $this->lostShips))
                {
                    continue;
                }

                $ret = $this->moveOneField($leadShip, $ship, $nextfield);
                if ($ret !== null) {
                    $msg = array_merge($msg, $ret);
                }
            }

            //Alarm-Rot Meldungen
            if ($leadShip->getSystem() !== null && !$leadShip->isOverSystem()) {
                $starSystem = $leadShip->getSystem();
                $shipsOnLocation = $this->shipRepository->getByInnerSystemLocation($starSystem->getId(), $leadShip->getPosX(), $leadShip->getPosY());

                $fleetIds = [];
                $fleetCount = 0;
                $singleShipCount = 0;

                foreach ($shipsOnLocation as $shipOnLocation) {
                    
                    $fleet = $shipOnLocation->getFleet();
                    
                    if ($fleet === null) {
                        if ($shipOnLocation->getAlertState() == ShipAlertStateEnum::ALERT_RED) {
                            $singleShipCount++;
                        }
                    }
                    else {
                        $fleetIdEintrag = $fleetIds[$fleet->getId()] ?? null;
                        if ($fleetIdEintrag === null) {
                            if ($fleet->getLeadShip()->getAlertState() == ShipAlertStateEnum::ALERT_RED) {
                                $fleetCount++;
                            }
                            $fleetIds[$fleet->getId()] = [];
                        }
                    }
                }

                if ($fleetCount == 1) {
                    $this->addInformation("In Sektor " . $leadShip->getPosX() . "|" . $leadShip->getPosY() . " befindet sich " . $fleetCount . " Flotte auf Alarm-Rot!");
                }
                if ($fleetCount > 1) {
                    $this->addInformation("In Sektor " . $leadShip->getPosX() . "|" . $leadShip->getPosY() . " befinden sich " . $fleetCount . " Flotten auf Alarm-Rot!");
                }
                if ($singleShipCount == 1) {
                    $this->addInformation("In Sektor " . $leadShip->getPosX() . "|" . $leadShip->getPosY() . " befindet sich " . $singleShipCount . " Einzelschiff auf Alarm-Rot!");
                }
                if ($singleShipCount > 1) {
                    $this->addInformation("In Sektor " . $leadShip->getPosX() . "|" . $leadShip->getPosY() . " befinden sich " . $singleShipCount . " Einzelschiffe auf Alarm-Rot!");
                }
            }
        }
        $this->addInformationMerge($msg);
        if ($this->isFleetMode() && $this->flightFields > 0) {
            $this->addInformation(
                sprintf(
                    _('Die Flotte fliegt in Sektor %d|%d ein'),
                    $this->getDestX(),
                    $this->getDestY()
                )
            );
        }
    }

    private function isDestinationArrived(ShipInterface $ship) : bool
    {
        return $this->getDestX() == $ship->getPosX() && $this->getDestY() == $ship->getPosY();
    }

    private function determineFlightMethod(ShipInterface $ship)
    {
        if ($this->getDestX() == $ship->getPosX()) {
            $oldy = $ship->getPosY();
            if ($this->getDestY() > $oldy) {
                return ShipEnum::FLY_DOWN;
            } else {
                return ShipEnum::FLY_UP;
            }
        }
        if ($this->getDestY() == $ship->getPosY()) {
            $oldx = $ship->getPosX();
            if ($this->getDestX() > $oldx) {
                return ShipEnum::FLY_RIGHT;
            } else {
                return ShipEnum::FLY_LEFT;
            }
        }
    }

    private function moveOneField(
        ShipInterface $leadShip,
        ShipInterface $ship,
        $nextField
    ): ?array {
        $msg = [];
        if (!$this->isFleetMode()) {
            if ($ship->getSystem() === null && !$ship->isWarpAble()) {
                $this->addInformation(_("Dieses Schiff verfügt über keinen Warpantrieb"));
                return null;
            }
            if ($ship->getEps() < $ship->getRump()->getFlightEcost()) {
                $this->addInformation(sprintf(_('Die %s hat nicht genug Energie für den Flug (%d benötigt)'),
                    $ship->getName(), $ship->getRump()->getFlightEcost()));
                return null;
            }
            if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() == 0) {
                $this->addInformation(sprintf(_('Es werden %d Crewmitglieder benötigt'),
                    $ship->getBuildplan()->getCrew()));
                return null;
            }
        }
        $ship->setDockedTo(null);
        if ($ship->getState() == ShipStateEnum::SHIP_STATE_REPAIR) {
            $ship->cancelRepair();
            $this->addInformation(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getId()));
        }
        if ($ship->getTraktorMode() == 2) {
            $this->addInformation("Die " . $ship->getName() . " wird von einem Traktorstrahl gehalten");
            if ($this->isFleetMode()) {
                if ($leadShip === $ship) {
                    $this->updateDestination($ship->getPosX(), $ship->getPosY());
                } else {
                    $this->leaveFleet($ship);
                }
            } else {
                $this->updateDestination($ship->getPosX(), $ship->getPosY());
            }
            return null;
        }
        if (!$this->isFleetMode() && !$ship->getWarpState() && $ship->getSystem() === null) {
            try {
                $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            } catch (ShipSystemException $e) {
                $this->addInformation(sprintf(_("Die %s kann den Warpantrieb nicht aktivieren"), $ship->getName()));
                return null;
            }
        }

        //WA aktivieren falls außerhalb
        if ($ship->getSystem() === null && !$ship->getWarpState()) {
            try {
                $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

                $msg[] = "Die " . $ship->getName() . " aktiviert den Warpantrieb";
            } catch (ShipSystemException $e) {
                $this->leaveFleet($ship);

                $msg[] = sprintf(
                    _('Die %s kann den Warpantrieb nicht aktivieren (%s|%s)'),
                    $ship->getName(),
                    $ship->getPosX(),
                    $ship->getPosY()
                );
                break;
            }
        }
        
        $flight_ecost = $ship->getRump()->getFlightEcost() + $nextfield->getFieldType()->getEnergyCosts();

        //zu wenig E zum weiterfliegen
        if ($ship->getEps() < $flight_ecost) {
            if ($this->isFleetMode()) {
                if ($ship === $leadShip) {
                    $this->updateDestination($ship->getPosX(), $ship->getPosY());
                    $msg[] = _("Das Flaggschiff hat nicht genügend Energie für den Weiterflug");
                    break;
                } else {
                    $this->leaveFleet($ship);
                    $msg[] = "Die " . $ship->getName() . " hat die Flotte aufgrund Energiemangels verlassen (" . $ship->getPosX() . "|" . $ship->getPosY() . ")";
                    break;
                }
            } else {
                $this->updateDestination($ship->getPosX(), $ship->getPosY());
                break;
            }
        }

        //nächstes Feld nicht passierbar
        if (!$nextfield->getFieldType()->getPassable()) {
            if (($this->isFleetMode() && $ship->isFleetLeader()) || !$this->isFleetMode()) {
                $msg[] = _("Das nächste Feld kann nicht passiert werden");
            }
            $this->updateDestination($ship->getPosX(), $ship->getPosY());
            break;
        }
        //Traktorstrahl Kosten
        if ($ship->isTraktorbeamActive() && $ship->getEps() < $ship->getTraktorShip()->getRump()->getFlightEcost() + 1) {
            $msg[] = "Der Traktorstrahl auf die " . $ship->getTraktorShip()->getName() . " wurde in Sektor " . $ship->getPosX() . "|" . $ship->getPosY() . " aufgrund Energiemangels deaktiviert";
            $ship->deactivateTraktorBeam();
            $this->privateMessageSender->send(
                (int)$ship->getUserId(),
                (int)$ship->getTraktorShip()->getUserId(),
                "Der auf die " . $ship->getTraktorShip()->getName() . " gerichtete Traktorstrahl wurde in SeKtor " . $ship->getSectorString() . " deaktiviert",
                \Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        }
        
        $met = 'fly' . $method;
        $this->$met($ship);
        if (!$this->isFleetMode() && $ship->getFleetId()) {
            $this->leaveFleet($ship);
            $msg[] = "Die " . $ship->getName() . " hat die Flotte verlassen (" . $ship->getPosX() . "|" . $ship->getPosY() . ")";
        }
        //Traktorstrahl ggf. deaktivieren
        if ($ship->isTraktorbeamActive()) {
            if ($ship->getTraktorShip()->getFleetId()) {
                $msg[] = sprintf(_('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde deaktiviert'),
                    $ship->getTraktorShip()->getName());
                $ship->deactivateTraktorBeam();
            } else {
                $ship->setEps($ship->getEps() - $ship->getTraktorShip()->getRump()->getFlightEcost());
                $this->$met($ship->getTraktorShip());
            }
        }
        $field = $this->getFieldData($leadShip, $ship->getPosX(), $ship->getPosY());
        //Einflugschaden Energiemangel
        if ($flight_ecost > $ship->getEps()) {
            $ship->setEps(0);
            if ($field->getFieldType()->getDamage()) {
                if ($ship->isTraktorbeamActive()) {
                    $msg[] = "Die " . $ship->getTraktorShip()->getName() . " wurde in Sektor " . $ship->getPosX() . "|" . $ship->getPosY() . " beschädigt";
                    $damageMsg = $this->applyDamage->damage(
                        new DamageWrapper($field->getFieldType()->getDamage()),
                        $ship->getTraktorShip()
                    );
                    $msg = array_merge($msg, $damageMsg);
                }
                $msg[] = "Die " . $ship->getName() . " wurde in Sektor " . $ship->getPosX() . "|" . $ship->getPosY() . " beschädigt";
                $damageMsg = $this->applyDamage->damage(
                    new DamageWrapper($field->getFieldType()->getDamage()),
                    $ship
                );
                $msg = array_merge($msg, $damageMsg);

                if ($ship->getTraktorShip()->getIsDestroyed()) {
                    $this->entryCreator->addShipEntry(
                        'Die ' . $ship->getTraktorShip()->getName() . ' wurde beim Einflug in Sektor ' . $ship->getTraktorShip()->getSectorString() . ' zerstört'
                    );

                    $this->shipRemover->destroy($ship->getTraktorShip());
                }
            }
        } else {
            $ship->setEps($ship->getEps() - $flight_ecost);
        }
        //Einflugschaden Feldschaden
        if ($field->getFieldType()->getSpecialDamage() && (($ship->getSystem() !== null && $field->getFieldType()->getSpecialDamageInnerSystem()) || ($ship->getSystem() === null && !$ship->getWarpState() && !$field->getFieldType()->getSpecialDamageInnerSystem()))) {
            if ($ship->isTraktorbeamActive()) {
                $msg[] = "Die " . $ship->getTraktorShip()->getName() . " wurde in Sektor " . $ship->getPosX() . "|" . $ship->getPosY() . " beschädigt";
                $damageMsg = $this->applyDamage->damage(
                    new DamageWrapper($field->getFieldType()->getDamage()),
                    $ship->getTraktorShip()
                );
                $msg = array_merge($msg, $damageMsg);
            }
            $msg[] = $field->getFieldType()->getName() . " in Sektor " . $ship->getPosX() . "|" . $ship->getPosY();
            $damageMsg = $this->applyDamage->damage(
                new DamageWrapper($field->getFieldType()->getSpecialDamage()),
                $ship
            );
            $msg = array_merge($msg, $damageMsg);

            if ($ship->getIsDestroyed()) {
                $this->entryCreator->addShipEntry(
                    'Die ' . $ship->getName() . ' wurde beim Einflug in Sektor ' . $ship->getSectorString() . ' zerstört'
                );

                $this->shipRemover->destroy($ship);
                $this->lostShips[$ship->getid()] = $ship;

                break;
            }
        }
        
        $this->shipRepository->save($ship);
        return $msg;
    }

    private function leaveFleet(ShipInterface $ship)
    {
        $ship->leaveFleet();
        $lostShips[$ship->getId()] = $ship;
    }

    private function getNextField(ShipInterface $leadShip, $method)
    {
        switch ($method) {
            case ShipEnum::FLY_RIGHT:
                return $this->getFieldData($leadShip, $leadShip->getPosX() + 1, $leadShip->getPosY());
            case ShipEnum::FLY_LEFT:
                return $this->getFieldData($leadShip, $leadShip->getPosX() - 1, $leadShip->getPosY());
            case ShipEnum::FLY_UP:
                return $this->getFieldData($leadShip, $leadShip->getPosX(), $leadShip->getPosY() - 1);
            case ShipEnum::FLY_DOWN:
                return $this->getFieldData($leadShip, $leadShip->getPosX(), $leadShip->getPosY() + 1);
        }
    }

    private function updateDestination(&$posx, &$posy)
    {
        $this->setDestX($posx);
        $this->setDestY($posy);
    }

    private function fly4(ShipInterface $ship)
    {
        $ship->setPosY($ship->getPosY() + 1);
        $ship->setFlightDirection(1);
    }

    private function fly3(ShipInterface $ship)
    {
        $ship->setPosY($ship->getPosY() - 1);
        $ship->setFlightDirection(2);
    }

    private function fly1(ShipInterface $ship)
    {
        $ship->setPosX($ship->getPosX() + 1);
        $ship->setFlightDirection(3);
    }

    private function fly2(ShipInterface $ship)
    {
        $ship->setPosX($ship->getPosX() - 1);
        $ship->setFlightDirection(4);
    }

    private function getFieldData(ShipInterface $leadShip, $x, $y)
    {
        if ($this->fieldData === null) {
            $sx = (int)$leadShip->getPosX();
            $sy = (int)$leadShip->getPosY();
            $destx = (int)$this->getDestX();
            $desty = (int)$this->getDestY();

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
}
