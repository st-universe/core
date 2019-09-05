<?php

use Stu\Lib\DamageWrapper;
use Stu\Orm\Entity\MapFieldTypeInterface;

class ShipMover {

	private $new_x = 0;
	private $new_y = 0;
	private $firstShip = NULL;
	private $fleetMode = 0;
	private $fieldData = NULL;
	private $fieldCount = NULL;
	private $flightFields = 0;

	function __construct(&$firstShip) {
		$this->firstShip = $firstShip;
		$this->setDestination();
		$this->determineFleetMode();
		$this->preMove();
	}

	private function getFlightFields() {
		return $this->flightFields;
	}

	function setDestination() {
		$posx = request::getIntFatal('posx');
		$posy = request::getIntFatal('posy');
		if ($this->getFirstShip()->getPosX() != $posx && $this->getFirstShip()->getPosY() != $posy) {
			new InvalidParamException;
		}
		if ($posx < 1) {
			$posx = 1;
		}
		if ($posy < 1) {
			$posy = 1;
		}
		if ($this->getFirstShip()->isInSystem()) {
			$sys = $this->getFirstShip()->getSystem();
			if ($posx > $sys->getMaxX()) {
				$posx = $sys->getMaxX();
			}
			if ($posy > $sys->getMaxY()) {
				$posy = $sys->getMaxY();
			}
		} else {
			if ($posx > MAP_MAX_X) {
				$posx = MAP_MAX_X;
			}
			if ($posy > MAP_MAX_Y) {
				$posy = MAP_MAX_Y;
			}
		}
		$this->setDestX($posx);
		$this->setDestY($posy);
	}

	function determineFleetMode() {
		if (!$this->getFirstShip()->isInFleet()) {
			return;
		}
		// check ob das erste schiff auch das flaggschiff ist
		if (!$this->getFirstShip()->isFleetLeader()) {
			return;
		}
		$this->setFleetMode(1);
	}

	function setFleetMode($value) {
		$this->fleetMode = $value;
	}

	function getFirstShip() {
		return $this->firstShip;
	}

	function isFleetMode() {
		return $this->fleetMode;
	}

	function getDestX() {
		return $this->new_x;
	}

	function getDestY() {
		return $this->new_y;
	}

	function calcFieldCount() {
		if ($this->getFirstShip()->getPosX() == $this->getDestX()) {
			$fields = abs($this->getFirstShip()->getPosY()-$this->getDestY());
		} else {
			$fields = abs($this->getFirstShip()->getPosX()-$this->getDestX());
		}
		if ($fields > $this->getFirstShip()->getEps()) {
			$fields = $this->getFirstShip()->getEps();
		}
		$this->setFieldCount($fields);
	}

	function setFieldCount($value) {
		$this->fieldCount = $value;
	}

	function getFieldCount() {
		if ($this->fieldCount === NULL) {
			$this->calcFieldCount();
		}
		return $this->fieldCount;
	}

	function setDestX($value) {
		$this->new_x = $value;
	}

	function setDestY($value) {
		$this->new_y = $value;
	}

	private $informations = array();

	function addInformation($value) {
		$this->informations[] = $value;
	}

	function addInformationMerge($value) {
		if (!is_array($value)) {
			return;
		}
		$this->informations = array_merge($this->getInformations(),$value);
	}

	function getInformations() {
		return $this->informations;
	}

	function isFirstShip(&$shipId) {
		return $shipId == $this->getFirstShip()->getId();
	}

	private function preMove() {
		$ships[] = &$this->getFirstShip();
		$msg = array();
		if ($this->isFleetMode()) {
			$fleetShips = Fleet::getShipsBy($this->getFirstShip()->getFleetId(),array($this->getFirstShip()->getId()));
			$ships = array_merge($ships,$fleetShips);
		}
		if ($this->isFleetMode()) {
			if ($this->getFirstShip()->getEps() == 0) {
				$this->addInformation(sprintf(_('Die %s hat nicht genug Energie für den Flug'),$this->getFirstShip()->getName()));
				return;
			}
			if ($this->getFirstShip()->getBuildplan()->getCrew() > 0 && $this->getFirstShip()->getCrew() == 0) {
				$this->addInformation(sprintf(_('Es werden %d Crewmitglieder benötigt'),$this->getFirstShip()->getBuildplan()->getCrew()));
				return;
			}
		}
		foreach($ships as $key => $obj) {
			$ret = $this->move($obj);
			if (is_array($ret)) {
				$msg = array_merge($msg,$ret);
			}
		}
		$this->addInformationMerge($msg);
		if ($this->isFleetMode() && $this->getFlightFields() > 0) {
			$this->addInformation(sprintf(_('Die Flotte fliegt in Sektor %d|%d ein'),$this->getDestX(),$this->getDestY()));
		}
	}

	private function move(&$ship) {
		$msg = array();
		if (!$this->isFleetMode()) {
			if (!$ship->isInSystem() && !$ship->isWarpAble()) {
				$this->addInformation(_("Dieses Schiff verfügt über keinen Warpantrieb"));
				return FALSE;	
			}
			if ($ship->getEps() < $ship->getRump()->getFlightEcost()) {
				$this->addInformation(sprintf(_('Die %s hat nicht genug Energie für den Flug (%d benötigt)'),$ship->getName(),$ship->getRump()->getFlightEcost()));
				return FALSE;
			}
			if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrew() == 0) {
				$this->addInformation(sprintf(_('Es werden %d Crewmitglieder benötigt'),$ship->getBuildplan()->getCrew()));
				return FALSE;
			}
		}
		$ship->setDock(0);
		if ($ship->getState() == SHIP_STATE_REPAIR) {
			$ship->cancelRepair();
			$this->addInformation(sprintf(_('Die Reparatur der %s wurde abgebrochen'),$ship->getId()));
		}
		if ($ship->getTraktorMode() == 2) {
			$this->addInformation("Die ".$ship->getName()." wird von einem Traktorstrahl gehalten");
			if ($this->isFleetMode()) {
				if ($this->isFirstShip($ship->getId())) {
					$this->stopMove($ship->getPosX(),$ship->getPosY());
				} else {
					$ship->leaveFleet();
				}
			} else {
				$this->stopMove($ship->getPosX(),$ship->getPosY());
			}
			return;
		}
		if (!$this->isFleetMode() && !$ship->getWarpState() && !$ship->isInSystem()) {
			if ($ship->getEps() < $ship->getShipSystem(SYSTEM_WARPDRIVE)->getEpsUsage()) {
				$this->addInformation(sprintf(_("Die %s kann den Warpantrieb aufgrund von Energiemangel nicht aktivieren"),$ship->getName()));
				return FALSE;
			}
			$ship->activateSystem(SYSTEM_WARPDRIVE);
			if ($ship->getTraktorMode() == 1) {
				if ($ship->getEps() < $ship->getTraktorShip()->getEpsUsage()) {
					$ship->deactivateTraktorBeam();
					$this->addInformation(sprintf(_("Der Traktorstrahl auf die %s wurde in Sektor %d|%d aufgrund Energiemangels deaktiviert"),$ship->getTraktorShip()->getName(),$ship->getPosX(),$ship->getPosY()));
				} else {
					$ship->getTraktorShip()->activateSystem(SYSTEM_WARPDRIVE,FALSE);
					$ship->lowerEps($ship->getTraktorShip()->getShipSystem(SYSTEM_WARPDRIVE)->getEpsUsage());
				}
			}
		}
		if ($this->getDestX() == $ship->getPosX() && $this->getDestY() == $ship->getPosY()) {
			return;
		}
		if ($this->getDestX() == $ship->getPosX()) {
			$oldy = $ship->getPosY();
			$cury = $oldy;
			if ($this->getDestY() > $oldy) {
				$method = FLY_DOWN;
			} else {
				$method = FLY_UP;
			}
		}
		if ($this->getDestY() == $ship->getPosY()) {
			$oldx = $ship->getPosX();
			$curx = $oldx;
			if ($this->getDestX() > $oldx) {
				$method = FLY_RIGHT;
			} else {
				$method = FLY_LEFT;
			}
		}
		$i = 1;
		while($i<=$this->getFieldCount()) {
			if (!$ship->isInSystem() && !$ship->getWarpState()) {
				if (!$ship->isWarpAble()) {
					$ship->leaveFleet();
					$msg[] = "Die ".$ship->getName()." verfügt über keinen Warpantrieb (".$ship->getPosX()."|".$ship->getPosY().")";
					break;
				}
				if (!$ship->getShipSystem(SYSTEM_WARPDRIVE)->isActivateable()) {
					$ship->leaveFleet();
					$msg[] = "Die ".$ship->getName()." kann den Warpantrieb nicht aktivieren (".$ship->getPosX()."|".$ship->getPosY().")";
					break;
				}
				if ($ship->getEps() < $ship->getShipSystem(SYSTEM_WARPDRIVE)->getEpsUsage()) {
					$ship->leaveFleet();
					$msg[] = "Die ".$ship->getName()." kann den Warpantrieb aufgrund Energiemangel nicht aktivieren (".$ship->getPosX()."|".$ship->getPosY().")";
					break;
				}
				$ship->activateSystem(SYSTEM_WARPDRIVE);
				$msg[] = "Die ".$ship->getName()." aktiviert den Warpantrieb";
			}
			$nextfield = $this->getNextField($method,$ship);
			$flight_ecost = $ship->getRump()->getFlightEcost()+$nextfield->getFieldType()->getEnergyCosts();
			if ($ship->getEps() < $flight_ecost) {
				if ($this->isFleetMode()) {
					if ($this->isFirstShip($ship->getId())) {
						$this->stopMove($ship->getPosX(),$ship->getPosY());
						$this->setFieldCount($i-1);
						$msg[] = _("Das Flaggschiff hat nicht genügend Energie für den Weiterflug");
						break;
					} else {
						$ship->leaveFleet();
						$msg[] = "Die ".$ship->getName()." hat die Flotte aufgrund Energiemangels verlassen (".$ship->getPosX()."|".$ship->getPosY().")";
						break;
					}
				} else {
					$this->stopMove($ship->getPosX(),$ship->getPosY());
					break;
				}
			}
			$i++;
			if (!$nextfield->getFieldType()->getPassable()) {
				if (($this->isFleetMode() && $ship->isFleetLeader()) || !$this->isFleetMode())
				$msg[] = _("Das nächste Feld kann nicht passiert werden");
				$this->stopMove($ship->getPosX(),$ship->getPosY());
				break;
			}
			if ($ship->isTraktorbeamActive() && $ship->getEps() < $ship->getTraktorShip()->getRump()->getFlightEcost()+1) {
				$msg[] = "Der Traktorstrahl auf die ".$ship->getTraktorShip()->getName()." wurde in Sektor ".$ship->getPosX()."|".$ship->getPosY()." aufgrund Energiemangels deaktiviert";
				$ship->deactivateTraktorBeam();
				PM::sendPM($ship->getUserId(),$ship->getTraktorShip()->getUserId(),"Der auf die ".$ship->getTraktorShip()->getName()." gerichtete Traktorstrahl wurde in SeKtor ".$ship->getSectorString()." deaktiviert",PM_SPECIAL_SHIP);
			}
			$this->flightDone = TRUE;
			$this->flightFields++;
			$met = 'fly'.$method;
			$this->$met($ship);
			if (!$this->isFleetMode() && $ship->isInFleet()) {
				$ship->leaveFleet();
				$msg[] = "Die ".$ship->getName()." hat die Flotte verlassen (".$ship->getPosX()."|".$ship->getPosY().")";
			}
			if ($ship->isTraktorbeamActive()) {
				if ($ship->getTraktorShip()->isInFleet()) {
					$msg[] = sprintf(_('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde deaktiviert'),$ship->getTraktorShip()->getName());
					$ship->deactivateTraktorBeam();
				} else {
					$ship->lowerEps($ship->getTraktorShip()->getRump()->getFlightEcost());
					$this->$met($ship->getTraktorShip());
				}
			}
			$field = $this->getFieldData($ship->getPosX(),$ship->getPosY());
			if ($flight_ecost > $ship->getEps()) {
				$ship->setEps(0);
				if ($field->getFieldType()->getDamage()) {
					if ($ship->isTraktorbeamActive()) {
						$msg[] = "Die ".$ship->getTraktorShip()->getName()." wurde in Sektor ".$ship->getPosX()."|".$ship->getPosY()." beschädigt";
						$damageMsg = $ship->getTraktorShip()->damage(new DamageWrapper($field->getFieldType()->getDamage()));
						$msg = array_merge($msg,$damageMsg);
					}
					$msg[] = "Die ".$ship->getName()." wurde in Sektor ".$ship->getPosX()."|".$ship->getPosY()." beschädigt";
					$damageMsg = $ship->damage(new DamageWrapper($field->getFieldType()->getDamage()));
					$msg = array_merge($msg,$damageMsg);
				}
			} else {
				$ship->lowerEps($flight_ecost);
			}
			if ($field->getFieldType()->getSpecialDamage() && (($ship->isInSystem() && $field->getFieldType()->getSpecialDamageInnerSystem()) || (!$ship->isInSystem() && !$ship->getWarpState() && !$field->getFieldType()->getSpecialDamageInnerSystem()))) {
				if ($ship->isTraktorbeamActive()) {
					$msg[] = "Die ".$ship->getTraktorShip()->getName()." wurde in Sektor ".$ship->getPosX()."|".$ship->getPosY()." beschädigt";
					$damageMsg = $ship->getTraktorShip()->damage(new DamageWrapper($field->getFieldType()->getDamage()));
					$msg = array_merge($msg,$damageMsg);
				}
				$msg[] = $field->getFieldType()->getName()." in Sektor ".$ship->getPosX()."|".$ship->getPosY();
				$damageMsg = $ship->damage(new DamageWrapper($field->getFieldType()->getSpecialDamage()));
				$msg = array_merge($msg,$damageMsg);
			}
		}
		if ($this->flightDone) {
			if (!$this->isFleetMode()) {
				$this->addInformation("Die ".$ship->getName()." fliegt in Sektor ".$ship->getPosX()."|".$ship->getPosY()." ein");
			}
			if ($ship->isTraktorbeamActive()) {
				$this->addInformation("Die ".$ship->getTraktorShip()->getName()." wurde per Traktorstrahl mitgezogen");
				$ship->getTraktorShip()->save();	
			}
		}
		$ship->save();
		return $msg;
	}

	private $flightDone = FALSE;

	private function getNextField(&$method,&$ship) {
		switch ($method) {
			case FLY_RIGHT:
				return $this->getFieldData($ship->getPosX()+1,$ship->getPosY());
			case FLY_LEFT:
				return $this->getFieldData($ship->getPosX()-1,$ship->getPosY());
			case FLY_UP:
				return $this->getFieldData($ship->getPosX(),$ship->getPosY()-1);
			case FLY_DOWN:
				return $this->getFieldData($ship->getPosX(),$ship->getPosY()+1);
		}
	}

	function stopMove(&$posx,&$posy) {
		$this->setDestX($posx);
		$this->setDestY($posy);
	}

	function fly4(&$ship) {
		$ship->setPosY($ship->getPosY()+1);
		$ship->setFlightDirection(1);
	}

	function fly3(&$ship) {
		$ship->setPosY($ship->getPosY()-1);
		$ship->setFlightDirection(2);
	}
	
	function fly1(&$ship) {
		$ship->setPosX($ship->getPosX()+1);
		$ship->setFlightDirection(3);
	}

	function fly2(&$ship) {
		$ship->setPosX($ship->getPosX()-1);
		$ship->setFlightDirection(4);
	}

	function getFieldData($x,$y) {
		if ($this->fieldData === NULL) {
			if (!$this->getFirstShip()->isInSystem()) {
				$this->fieldData = MapField::getFieldsByFlightRoute($this->getFirstShip()->getPosX(),
			    	       $this->getFirstShip()->getPosY(),
			               $this->getDestX(),$this->getDestY());	
			} else {
				$this->fieldData = SystemMap::getFieldsByFlightRoute($this->getFirstShip()->getSystemsId(),
				       $this->getFirstShip()->getPosX(),
			    	       $this->getFirstShip()->getPosY(),
			               $this->getDestX(),$this->getDestY());	
			}

		}
		return $this->fieldData[$x."_".$y];
	}
}
?>
