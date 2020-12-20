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

            // move every ship by one field
            foreach ($ships as $ship) {
                if (!array_key_exists($ship->getId(), $this->lostShips)
                    && !$this->isDestinationArrived($leadShip))
                {
                    $this->moveOneField($leadShip, $ship);
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
        foreach ($ships as $ship)
        {
            if (!$ship->getIsDestroyed())
            {
                $this->shipRepository->save($ship);
            }
            if ($ship->isTraktorbeamActive()) {
                $this->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde per Traktorstrahl mitgezogen");
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
        }
        else {
            $this->addInformation("Die " . $leadShip->getName() . " fliegt in Sektor " . $leadShip->getPosX() . "|" . $leadShip->getPosY() . " ein");
        }
    }

    private function getReadyForFlight(ShipInterface $leadShip, array $ships) : void
    {
        foreach ($ships as $ship) {
            $ship->setDockedTo(null);
            if ($ship->getState() == ShipStateEnum::SHIP_STATE_REPAIR) {
                $ship->cancelRepair();
                $this->addInformation(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getId()));
            }
            if ($ship->isTraktorbeamActive() && $ship->getTraktorShip()->getFleetId()) {
                $this->deactivateTraktorBeam($ship,
                    sprintf(_('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde deaktiviert'),
                        $ship->getTraktorShip()->getName()));
            }
            if ($ship->getTraktorMode() == 2) {
                $this->addLostShip($ship, $leadShip, "Die " . $ship->getName() . " wird von einem Traktorstrahl gehalten");
                continue;
            }
            // WA vorhanden?
            if ($ship->getSystem() === null && !$ship->isWarpAble()) {
                $this->addLostShip($ship, $leadShip, "Die " . $ship->getName() . " verfügt über keinen Warpantrieb");
                continue;
            }
            //WA aktivieren falls außerhalb
            if ($ship->getSystem() === null && !$ship->getWarpState()) {
                try {
                    $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

                    $this->addInformation("Die " . $ship->getName() . " aktiviert den Warpantrieb");
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
                $this->addLostShip($ship, $leadShip, sprintf(_('Die %s hat nicht genug Energie für den Flug'),
                                                        $ship->getName()));
                continue;
            }
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
        ShipInterface $ship
    ) {
        // zu wenig Crew?
        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() == 0) {
            $this->addLostShip($ship, $leadShip,
                sprintf(_('Es werden %d Crewmitglieder benötigt'),
                    $ship->getBuildplan()->getCrew()));
            return;
        }
        
        $nextfield = $this->getNextField($leadShip, $method, $ship);
        $flight_ecost = $ship->getRump()->getFlightEcost() + $nextfield->getFieldType()->getEnergyCosts();
        
        //zu wenig E zum weiterfliegen
        if ($ship->getEps() < $flight_ecost) {
            $this->addLostShip($ship, $leadShip,
                sprintf(_('Die %s hat nicht genug Energie für den Flug (%d benötigt)'),
                    $ship->getName(), $ship->getRump()->getFlightEcost()));
            return;
        }

        //nächstes Feld nicht passierbar
        if (!$nextfield->getFieldType()->getPassable()) {
            $this->addLostShip($ship, $leadShip, "Das nächste Feld kann nicht passiert werden");
            return;
        }

        //Traktorstrahl Kosten
        if ($ship->isTraktorbeamActive() && $ship->getEps() < $ship->getTraktorShip()->getRump()->getFlightEcost() + 1) {
            $this->deactivateTraktorBeam($ship, "Der Traktorstrahl auf die " . $ship->getTraktorShip()->getName() . " wurde in Sektor " . $ship->getPosX() . "|" . $ship->getPosY() . " aufgrund Energiemangels deaktiviert");
        }
        
        $met = 'fly' . $method;
        $this->$met($ship);
        if (!$this->isFleetMode() && $ship->getFleetId()) {
            $this->leaveFleet($ship);
        }
        //Traktorstrahl Energie abziehen
        if ($ship->isTraktorbeamActive()) {
            $ship->setEps($ship->getEps() - $ship->getTraktorShip()->getRump()->getFlightEcost());
            $this->$met($ship->getTraktorShip());
        }
        $field = $this->getFieldData($leadShip, $ship->getPosX(), $ship->getPosY());

        //Einflugschaden Energiemangel
        if ($flight_ecost > $ship->getEps()) {
            $ship->setEps(0);
            if ($field->getFieldType()->getDamage()) {
                if ($ship->isTraktorbeamActive()) {
                    $this->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde in Sektor " . $ship->getPosX() . "|" . $ship->getPosY() . " beschädigt");
                    $damageMsg = $this->applyDamage->damage(
                        new DamageWrapper($field->getFieldType()->getDamage()),
                        $ship->getTraktorShip()
                    );
                    $this->addInformationMerge($damageMsg);
                }
                $this->addInformation("Die " . $ship->getName() . " wurde in Sektor " . $ship->getPosX() . "|" . $ship->getPosY() . " beschädigt");
                $damageMsg = $this->applyDamage->damage(
                    new DamageWrapper($field->getFieldType()->getDamage()),
                    $ship
                );
                $this->addInformationMerge($damageMsg);

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
                $this->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde in Sektor " . $ship->getPosX() . "|" . $ship->getPosY() . " beschädigt");
                $damageMsg = $this->applyDamage->damage(
                    new DamageWrapper($field->getFieldType()->getDamage()),
                    $ship->getTraktorShip()
                );
                $this->addInformationMerge($damageMsg);
            }
            $this->addInformation($field->getFieldType()->getName() . " in Sektor " . $ship->getPosX() . "|" . $ship->getPosY());
            $damageMsg = $this->applyDamage->damage(
                new DamageWrapper($field->getFieldType()->getSpecialDamage()),
                $ship
            );
            $this->addInformationMerge($damageMsg);

            if ($ship->getIsDestroyed()) {
                $this->entryCreator->addShipEntry(
                    'Die ' . $ship->getName() . ' wurde beim Einflug in Sektor ' . $ship->getSectorString() . ' zerstört'
                );

                $this->shipRemover->destroy($ship);
                $this->lostShips[$ship->getid()] = $ship;

                return;
            }
        }
    }

    private function deactivateTraktorBeam(ShipInterface $ship, string $msg)
    {
        $this->addInformation($msg);
        $this->privateMessageSender->send(
            (int)$ship->getUserId(),
            (int)$ship->getTraktorShip()->getUserId(),
            "Der auf die " . $ship->getTraktorShip()->getName() . " gerichtete Traktorstrahl wurde in SeKtor " . $ship->getSectorString() . " deaktiviert",
            \Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );
        $ship->deactivateTraktorBeam();
    }

    private function addLostShip(ShipInterface $ship, ShipInterface $leadShip, string $msg)
    {
        $this->addInformation($msg);
        
        $this->lostShips[$ship->getId()] = $ship;
        
        if ($ship === $leadShip)
        {
            $this->updateDestination($ship->getPosX(), $ship->getPosY());
        }
        else {
            $this->leaveFleet($ship);
        }
    }
    
    private function leaveFleet(ShipInterface $ship)
    {
        $ship->leaveFleet();
        
        $this->addInformation("Die " . $ship->getName() . " hat die Flotte verlassen (" . $ship->getPosX() . "|" . $ship->getPosY() . ")");
    }

    private function getNextField(ShipInterface $leadShip, $method, ShipInterface $ship)
    {
        switch ($method) {
            case ShipEnum::FLY_RIGHT:
                return $this->getFieldData($leadShip, $ship->getPosX() + 1, $ship->getPosY());
            case ShipEnum::FLY_LEFT:
                return $this->getFieldData($leadShip, $ship->getPosX() - 1, $ship->getPosY());
            case ShipEnum::FLY_UP:
                return $this->getFieldData($leadShip, $ship->getPosX(), $ship->getPosY() - 1);
            case ShipEnum::FLY_DOWN:
                return $this->getFieldData($leadShip, $ship->getPosX(), $ship->getPosY() + 1);
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
