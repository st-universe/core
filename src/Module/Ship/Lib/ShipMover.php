<?php

namespace Stu\Module\Ship\Lib;

use InvalidParamException;
use Stu\Component\Map\MapEnum;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShipMover implements ShipMoverInterface
{
    private $mapRepository;

    private $starSystemMapRepository;

    private $shipRepository;

    private $entryCreator;

    private $shipRemover;

    private $privateMessageSender;

    private $shipSystemManager;

    private $applyDamage;

    private $new_x = 0;
    private $new_y = 0;
    private $fleetMode = 0;
    private $fieldData = null;
    private $fieldCount = null;
    private $flightFields = 0;

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

    private function determineFleetMode(ShipInterface $leadShip): void
    {
        if ($leadShip->getFleet() === null) {
            return;
        }
        // check ob das erste schiff auch das flaggschiff ist
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

    private function getFieldCount(ShipInterface $leadShip): int
    {
        if ($leadShip->getPosX() == $this->getDestX()) {
            $fields = abs($leadShip->getPosY() - $this->getDestY());
        } else {
            $fields = abs($leadShip->getPosX() - $this->getDestX());
        }
        $energyCosts = $leadShip->getRump()->getFlightEcost();

        if ($fields * $energyCosts > $leadShip->getEps()) {
            $fields = (int)floor($leadShip->getEps() / $energyCosts);
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

    private $informations = [];

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
        // @todo
        $this->setDestination($leadShip, $destinationX, $destinationY);
        $this->determineFleetMode($leadShip);

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
        foreach ($ships as $key => $obj) {
            $ret = $this->move($leadShip, $obj);
            if ($ret !== null) {
                $msg = array_merge($msg, $ret);
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

    private function move(
        ShipInterface $leadShip,
        ShipInterface $ship
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
                    $this->stopMove($ship->getPosX(), $ship->getPosY());
                } else {
                    $ship->leaveFleet();
                }
            } else {
                $this->stopMove($ship->getPosX(), $ship->getPosY());
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
        if ($this->getDestX() == $ship->getPosX() && $this->getDestY() == $ship->getPosY()) {
            return null;
        }
        if ($this->getDestX() == $ship->getPosX()) {
            $oldy = $ship->getPosY();
            $cury = $oldy;
            if ($this->getDestY() > $oldy) {
                $method = ShipEnum::FLY_DOWN;
            } else {
                $method = ShipEnum::FLY_UP;
            }
        }
        if ($this->getDestY() == $ship->getPosY()) {
            $oldx = $ship->getPosX();
            $curx = $oldx;
            if ($this->getDestX() > $oldx) {
                $method = ShipEnum::FLY_RIGHT;
            } else {
                $method = ShipEnum::FLY_LEFT;
            }
        }

        $fieldCount = $this->getFieldCount($leadShip);
        $i = 1;
        while ($i <= $fieldCount) {
            if ($ship->getSystem() === null && !$ship->getWarpState()) {
                try {
                    $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

                    $msg[] = "Die " . $ship->getName() . " aktiviert den Warpantrieb";
                } catch (ShipSystemException $e) {
                    $ship->leaveFleet();

                    $msg[] = sprintf(
                        _('Die %s kann den Warpantrieb nicht aktivieren (%s|%s)'),
                        $ship->getName(),
                        $ship->getPosX(),
                        $ship->getPosY()
                    );
                    break;
                }
            }
            $nextfield = $this->getNextField($leadShip, $method, $ship);
            $flight_ecost = $ship->getRump()->getFlightEcost() + $nextfield->getFieldType()->getEnergyCosts();
            if ($ship->getEps() < $flight_ecost) {
                if ($this->isFleetMode()) {
                    if ($ship === $leadShip) {
                        $this->stopMove($ship->getPosX(), $ship->getPosY());
                        $this->fieldCount = $i - 1;
                        $msg[] = _("Das Flaggschiff hat nicht genügend Energie für den Weiterflug");
                        break;
                    } else {
                        $ship->leaveFleet();
                        $msg[] = "Die " . $ship->getName() . " hat die Flotte aufgrund Energiemangels verlassen (" . $ship->getPosX() . "|" . $ship->getPosY() . ")";
                        break;
                    }
                } else {
                    $this->stopMove($ship->getPosX(), $ship->getPosY());
                    break;
                }
            }

            $i++;
            if (!$nextfield->getFieldType()->getPassable()) {
                if (($this->isFleetMode() && $ship->isFleetLeader()) || !$this->isFleetMode()) {
                    $msg[] = _("Das nächste Feld kann nicht passiert werden");
                }
                $this->stopMove($ship->getPosX(), $ship->getPosY());
                break;
            }
            if ($ship->isTraktorbeamActive() && $ship->getEps() < $ship->getTraktorShip()->getRump()->getFlightEcost() + 1) {
                $msg[] = "Der Traktorstrahl auf die " . $ship->getTraktorShip()->getName() . " wurde in Sektor " . $ship->getPosX() . "|" . $ship->getPosY() . " aufgrund Energiemangels deaktiviert";
                $ship->deactivateTraktorBeam();
                $this->privateMessageSender->send(
                    (int)$ship->getUserId(),
                    (int)$ship->getTraktorShip()->getUserId(),
                    "Der auf die " . $ship->getTraktorShip()->getName() . " gerichtete Traktorstrahl wurde in SeKtor " . $ship->getSectorString() . " deaktiviert",
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
                );
            }
            $this->flightDone = true;
            $this->flightFields++;
            $met = 'fly' . $method;
            $this->$met($ship);
            if (!$this->isFleetMode() && $ship->getFleetId()) {
                $ship->leaveFleet();
                $msg[] = "Die " . $ship->getName() . " hat die Flotte verlassen (" . $ship->getPosX() . "|" . $ship->getPosY() . ")";
            }
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
                }
            }
        }

        if ($this->flightDone) {
            if (!$this->isFleetMode()) {
                $this->addInformation("Die " . $ship->getName() . " fliegt in Sektor " . $ship->getPosX() . "|" . $ship->getPosY() . " ein");
            }
            if ($ship->isTraktorbeamActive()) {
                $this->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde per Traktorstrahl mitgezogen");
                $this->shipRepository->save($ship->getTraktorShip());
            }
        }
        $this->shipRepository->save($ship);
        return $msg;
    }

    private $flightDone = false;

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

    private function stopMove(&$posx, &$posy)
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
