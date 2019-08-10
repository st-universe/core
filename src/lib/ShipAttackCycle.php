<?php

class ShipAttackCycle {

	private $attacker = array();
	private $defender = array();
	private $firstStrike = 1;
	private $attackShip = NULL;
	private $defendShip = NULL;
	private $messages = array();
	private $usedShips = array('attacker' => array(),'defender' => array());
	private $attackFleetId = NULL;
	private $defendFleetId = NULL;

	function __construct(&$attacker,&$defender,&$attackFleetId,&$defendFleetId) {
		if (is_object($attacker)) {
			$this->attacker[$attacker->getId()] = &$attacker;
		} else {
			$this->attacker = &$attacker;
		}
		if (is_object($defender)) {
			$this->defender[$defender->getId()] = &$defender;
		} else {
			$this->defender = &$defender;
		}
		$this->attackFleetId = $attackFleetId;
		$this->defendFleetId = $defendFleetId;
		$this->cycle();
	}

	private function getAttacker() {
		return $this->attacker;
	}

	private function getDefender() {
		return $this->defender;
	}

	private function getFirstStrike() {
		return $this->firstStrike;
	}

	private function setFirstStrike($value) {
		$this->firstStrike = $value;
	}

	private function getAttackShip() {
		return $this->attackShip;
	}

	private function getDefendShip() {
		return $this->defendShip;
	}

	private function getAttackFleetId() {
		return $this->attackFleetId;
	}

	private function getDefendFleetId() {
		return $this->defendFleetId;
	}

	private function cycle() {
		while ($this->hasReadyAttacker() || $this->hasReadyDefender()) {
			$this->defineContrabants();
			if (!$this->getAttackShip() || !$this->getDefendShip()) {
				return;
			}
			if ($this->getAttackShip()->getDestroyed() || $this->getDefendShip()->getDestroyed()) {
				continue;
			}
			if ($this->getFirstStrike()) {
				$this->setFirstStrike(0);
			}
			$msg = $this->getAttackShip()->alertLevelBasedReaction();
			if ($msg) {
				$this->addMessage("Aktionen der ".$this->getAttackShip()->getName());
				$this->addMessageMerge($msg);
				$msg = array();
			}
			if (!$this->getAttackShip()->canFire()) {
				$this->getAttackShip()->save();
				continue;
			}
			if ($this->getDefendShip()->getWarpState()) {
				$this->getDefendShip()->deactivateSystem(SYSTEM_WARPDRIVE);
			}
			$this->getDefendShip()->cancelRepair();
			$this->getAttackShip()->cancelRepair();
			
			//--------------------------------------

			// Phaser
			if ($this->getAttackShip()->phaserIsActive()) {
				for ($i=1;$i<=$this->getAttackShip()->getRump()->getPhaserVolleys();$i++) {
					if (!$this->getAttackShip()->phaserIsActive() || $this->getAttackShip()->getEps() < $this->getAttackShip()->getPhaserEpsCost()) {
						break;
					}
					$this->getAttackShip()->lowerEps($this->getAttackShip()->getPhaserEpsCost());
					if ($this->getAttackShip()->getPhaser()->getFiringMode() == FIRINGMODE_RANDOM) {
						$this->redefineDefender();
						if (!$this->getDefendShip()) {
							$this->endCycle();
							break;
						}
					}
					$this->addMessage("Die ".$this->getAttackShip()->getName()." feuert mit einem ".$this->getAttackShip()->getPhaser()->getName()." auf die ".$this->getDefendShip()->getName());
					if ($this->getAttackShip()->getHitChance()*(100-$this->getDefendShip()->getEvadeChance()) < rand(1,10000)) {
						$this->addMessage("Die ".$this->getDefendShip()->getName()." wurde verfehlt");
						$this->endCycle();
						continue;
					}
					$damage_wrapper = new DamageWrapper($this->getAttackShip()->getPhaserDamage(),$this->getAttackShip()); {
						$damage_wrapper->setShieldDamageFactor($this->getAttackShip()->getRump()->getPhaserShieldDamageFactor());
						$damage_wrapper->setHullDamageFactor($this->getAttackShip()->getRump()->getPhaserHullDamageFactor());
						$damage_wrapper->setIsPhaserDamage(TRUE);
					}
					$this->addMessageMerge($this->getDefendShip()->damage($damage_wrapper));
					if ($this->getDefendShip()->isDestroyed()) {
						HistoryEntry::addEntry('Die '.$this->getDefendShip()->getName().' wurde in Sektor '.$this->getDefendShip()->getSectorString().' von der '.$this->getAttackShip()->getName().' zerstört');
						$this->getDefendShip()->destroy();
						$this->unsetDefender();
						$this->redefineDefender();
						if (!$this->getDefendShip()) {
							$this->endCycle();
							break;
						}
						if ($this->getAttackShip()->getPhaser()->getFiringMode() == FIRINGMODE_FOCUS) {
							$this->endCycle();
							break;
						}
					}
				}
			}
			if (!$this->getDefendShip()) {
				$this->endCycle();
				break;
			}
			// Torpedo
			if (!$this->getAttackShip()->torpedoIsActive()) {
				$this->endCycle($msg);
				continue;
			}
			if ($this->getDefendShip()->isDestroyed()) {
				$this->redefineDefender();
				if (!$this->getDefendShip()) {
					$this->endCycle();
					break;
				}
			}
			for ($i=1;$i<=$this->getAttackShip()->getRump()->getTorpedoVolleys();$i++) {
				if (!$this->getAttackShip()->torpedoIsActive() || $this->getAttackShip()->getEps() < $this->getAttackShip()->getTorpedoEpsCost()) {
					break;
				}
				$this->getAttackShip()->lowerTorpedo();
				$this->getAttackShip()->lowerEps($this->getAttackShip()->getTorpedoEpsCost());
				$this->redefineDefender();
				$this->addMessage("Die ".$this->getAttackShip()->getName()." feuert einen ".$this->getAttackShip()->getTorpedo()->getName()." auf die ".$this->getDefendShip()->getName());
				// higher evade chance for pulseships against 
				// torpedo ships
				if ($this->getAttackShip()->getRump()->getRoleId() == ROLE_TORPEDOSHIP && $this->getDefendShip()->getRump()->getRoleId() == ROLE_PULSESHIP) {
					$hitchance = round($this->getAttackShip()->getHitChance()*0.65);
				} else {
					$hitchance = $this->getAttackShip()->getHitChance();
				}
				if ($hitchance*(100-$this->getDefendShip()->getEvadeChance()) < rand(1,10000)) {
					$this->addMessage("Die ".$this->getDefendShip()->getName()." wurde verfehlt");
					continue;
				}
				$damage_wrapper = new DamageWrapper($this->getAttackShip()->getTorpedoDamage(),$this->getAttackShip()); {
					$damage_wrapper->setShieldDamageFactor($this->getAttackShip()->getTorpedo()->getShieldDamageFactor());
					$damage_wrapper->setHullDamageFactor($this->getAttackShip()->getTorpedo()->getHullDamageFactor());
					$damage_wrapper->setIsTorpedoDamage(TRUE);
				}
				$this->addMessageMerge($this->getDefendShip()->damage($damage_wrapper));
				if ($this->getDefendShip()->isDestroyed()) {
					$this->unsetDefender();
					HistoryEntry::addEntry('Die '.$this->getDefendShip()->getName().' wurde in Sektor '.$this->getDefendShip()->getSectorString().' von der '.$this->getAttackShip()->getName().' zerstört');
					$this->getDefendShip()->destroy();
					break;
				}
			}
			$this->endCycle();
		}
	}

	/**
	 */
	private function endCycle(&$msg=array()) { #{{{
		$this->addMessageMerge($msg);

		$this->getAttackShip()->save();
		if ($this->getDefendShip()) {
			$this->getDefendShip()->save();
		}
	} # }}}

	/**
	 */
	private function redefineDefender() { #{{{
		$this->getDefendShip()->save();
		if (array_key_exists($this->getDefendShip()->getId(),$this->getAttacker())) {
			$this->defendShip = &$this->getRandomAttacker();
			return;
		}
		if (!array_key_exists($this->getAttackShip()->getId(),$this->getDefender())) {
			$this->defendShip = &$this->getRandomDefender();
			return;
		}
		$this->defendShip = FALSE;
	} # }}}

	private function defineContrabants() {
		if ($this->getFirstStrike() || $this->isSingleMode()) {
			$this->attackShip = &$this->getRandomReadyAttacker();
			$this->defendShip = &$this->getRandomDefender();
			return TRUE;
		}
		$attReady = $this->hasReadyAttacker();
		$defReady = $this->hasReadyDefender();
		if ($attReady && !$defReady) {
			$this->attackShip = &$this->getRandomReadyAttacker();
			$this->defendShip = &$this->getRandomDefender();
			return TRUE;
		}
		if (!$attReady && $defReady) {
			$this->attackShip = &$this->getRandomReadyDefender();
			$this->defendShip = &$this->getRandomAttacker();
			return TRUE;
		}
		// XXX: TBD
		if (rand(1,2) == 1) {
			$this->attackShip = &$this->getRandomReadyAttacker();
			$this->defendShip = &$this->getRandomDefender();
		} else {
			$this->attackShip = &$this->getRandomReadyDefender();
			$this->defendShip = &$this->getRandomAttacker();
		}
		return TRUE;
	}

	private function getRandomDefender() {
		$count = count($this->getDefender());
		if ($count == 0) {
			return FALSE;
		}
		if ($count == 1) {
			$arr = &current($this->getDefender());
			if ($arr->isDestroyed()) {
				return FALSE;
			}
			if ($arr->isDisabled()) {
				$this->addMessage(_("Die ".$arr->getName()." ist kampfunfähig"));
				return FALSE;
			}
			return $arr;
		}
		$key = array_rand($this->getDefender());
		$defender = &$this->getDefender();
		return $defender[$key];
	}

	private function getRandomReadyDefender() {
		$arr = &$this->getDefender();
		shuffle($arr);
		foreach ($arr as $key => $obj) {
			if ($obj->isDestroyed()) {
				unset($arr[$key]);
				continue;
			}
			if ($obj->isDisabled()) {
				$this->addMessage(_("Die ".$obj->getName()." ist kampfunfähig"));
				return FALSE;
			}
			if (!$this->hasShot('defender',$obj->getId())) {
				$this->setHasShot('defender',$obj->getId());
				return $obj;
			}
		}
		return FALSE;
	}

	/**
	 */
	private function unsetDefender() { #{{{
		if (array_key_exists($this->getDefendShip()->getId(),$this->getAttacker())) {
			$arr = &$this->getAttacker();
			unset($arr[$this->getDefendShip()->getId()]);
			$this->attacker = &$arr;
			return;
		}
		$arr = &$this->getDefender();
		unset($arr[$this->getDefendShip()->getId()]);
		$this->defender = &$arr;
	} # }}}

	private function hasReadyAttacker() {
		return $this->getUsedShipCount('attacker') < count($this->getAttacker());
	}

	private function hasReadyDefender() {
		return $this->getUsedShipCount('defender') < count($this->getDefender());
	}

	private function getRandomAttacker() {
		$count = count($this->getAttacker());
		if ($count == 0) {
			return FALSE;
		}
		if ($count == 1) {
			$arr = &current($this->getAttacker());
			if ($arr->isDestroyed()) {
				return FALSE;
			}
			if ($arr->isDisabled()) {
				$this->addMessage(_("Die ".$arr->getName()." ist kampfunfähig"));
				return FALSE;
			}
			return $arr;
		}
		$attacker = &$this->getAttacker();
		$key = array_rand($attacker);
		return $attacker[$key];
	}

	private function getRandomReadyAttacker() {
		$arr = &$this->getAttacker();
		shuffle($arr);
		foreach ($arr as $key => $obj) {
			if ($obj->isDestroyed() || $obj->isDisabled()) {
				unset($arr[$key]);
				continue;
			}
			if ($obj->isDisabled()) {
				$this->addMessage(_("Die ".$obj->getName()." ist kampfunfähig"));
				unset($arr[$key]);
				return FALSE;
			}
			if (!$this->hasShot('attacker',$obj->getId())) {
				$this->setHasShot('attacker',$obj->getId());
				return $obj;
			}
		}
		return FALSE;
	}

	private function hasShot($key,$value) {
		return array_key_exists($value,$this->getUsedShips($key));
	}

	private function setHasShot($key,$value) {
		$this->usedShips[$key][$value] = TRUE;
	}

	private function getUsedShips($key) {
		return $this->usedShips[$key];
	}
	
	private function getUsedShipCount($key) {
		return count($this->getUsedShips($key));
	}

	private function addMessageMerge($msg) {
		$this->messages = array_merge($this->getMessages(),$msg);
	}

	private function addMessage($msg) {
		$this->messages[] = $msg;
	}

	public function getMessages() {
		return $this->messages;
	}

	protected function isSingleMode() {
		return FALSE;
	}

}

class ShipSingleAttackCycle extends ShipAttackCycle {

	protected function isSingleMode() {
		return TRUE;
	}

}
