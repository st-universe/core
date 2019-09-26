<?php

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipRoleEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\WeaponInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\WeaponRepositoryInterface;

class ShipAttackCycle {

    public const FIRINGMODE_FOCUS = 2;
    public const FIRINGMODE_RANDOM = 1;
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

	private function getAttackShip(): ShipInterface {
		return $this->attackShip;
	}

	private function getDefendShip(): ShipInterface {
		return $this->defendShip;
	}

	private function getEntryCreator(): EntryCreatorInterface {
	    // @todo refactor
        global $container;

        return $container->get(EntryCreatorInterface::class);
    }

    private function getShipRemover(): ShipRemoverInterface
    {
        // @todo refactor
        global $container;

        return $container->get(ShipRemoverInterface::class);
    }

    private function cycle() {
	    // @todo refactor
        global $container;

        $shipRepo = $container->get(ShipRepositoryInterface::class);

		while ($this->hasReadyAttacker() || $this->hasReadyDefender()) {
			$this->defineContrabants();
			if (!$this->getAttackShip() || !$this->getDefendShip()) {
				return;
			}
			if ($this->getAttackShip()->getIsDestroyed() || $this->getDefendShip()->getIsDestroyed()) {
				continue;
			}
			if ($this->getFirstStrike()) {
				$this->setFirstStrike(0);
			}
			$msg = $this->alertLevelBasedReaction($this->getAttackShip());
			if ($msg) {
				$this->addMessage("Aktionen der ".$this->getAttackShip()->getName());
				$this->addMessageMerge($msg);
				$msg = array();
			}
			if (!$this->canFire($this->getAttackShip())) {
				$shipRepo->save($this->getAttackShip());
				continue;
			}
			if ($this->getDefendShip()->getWarpState()) {
				$this->getDefendShip()->deactivateSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
			}
			$this->getDefendShip()->cancelRepair();
			$this->getAttackShip()->cancelRepair();

			//--------------------------------------

			// Phaser
			if ($this->getAttackShip()->getPhaser()) {
				for ($i=1;$i<=$this->getAttackShip()->getRump()->getPhaserVolleys();$i++) {
					if (!$this->getAttackShip()->getPhaser() || $this->getAttackShip()->getEps() < $this->getEnergyWeaponEnergyCosts()) {
						break;
					}
					$this->getAttackShip()->setEps($this->getAttackShip()->getEps() - $this->getEnergyWeaponEnergyCosts());
					if ($this->getEnergyWeapon($this->getAttackShip())->getFiringMode() == self::FIRINGMODE_RANDOM) {
						$this->redefineDefender();
						if (!$this->getDefendShip()) {
							$this->endCycle();
							break;
						}
					}
					$this->addMessage("Die ".$this->getAttackShip()->getName()." feuert mit einem ".$this->getEnergyWeapon($this->getAttackShip())->getName()." auf die ".$this->getDefendShip()->getName());
					if ($this->getAttackShip()->getHitChance()*(100-$this->getDefendShip()->getEvadeChance()) < rand(1,10000)) {
						$this->addMessage("Die ".$this->getDefendShip()->getName()." wurde verfehlt");
						$this->endCycle();
						continue;
					}
					$damage_wrapper = new DamageWrapper($this->getEnergyWeaponDamage($this->getAttackShip()),$this->getAttackShip()); {
						$damage_wrapper->setShieldDamageFactor($this->getAttackShip()->getRump()->getPhaserShieldDamageFactor());
						$damage_wrapper->setHullDamageFactor($this->getAttackShip()->getRump()->getPhaserHullDamageFactor());
						$damage_wrapper->setIsPhaserDamage(TRUE);
					}
					$this->addMessageMerge($this->getDefendShip()->damage($damage_wrapper));
					if ($this->getDefendShip()->getIsDestroyed()) {
						$this->getEntryCreator()->addShipEntry(
						    'Die '.$this->getDefendShip()->getName().' wurde in Sektor '.$this->getDefendShip()->getSectorString().' von der '.$this->getAttackShip()->getName().' zerstört',
                            (int) $this->getAttackShip()->getUserId()
                        );
						$this->getShipRemover()->destroy($this->getDefendShip());
						$this->unsetDefender();
						$this->redefineDefender();
						if (!$this->getDefendShip()) {
							$this->endCycle();
							break;
						}
						if ($this->getEnergyWeapon($this->getAttackShip())->getFiringMode() == self::FIRINGMODE_FOCUS) {
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
			if (!$this->getAttackShip()->getTorpedos()) {
				$this->endCycle($msg);
				continue;
			}
			if ($this->getDefendShip()->getIsDestroyed()) {
				$this->redefineDefender();
				if (!$this->getDefendShip()) {
					$this->endCycle();
					break;
				}
			}
			for ($i=1;$i<=$this->getAttackShip()->getRump()->getTorpedoVolleys();$i++) {
				if (!$this->getAttackShip()->getTorpedos() || $this->getAttackShip()->getEps() < $this->getProjectileWeaponEnergyCosts()) {
					break;
				}
				$this->getAttackShip()->setTorpedoCount($this->getAttackShip()->getTorpedoCount() - 1);
				if ($this->getAttackShip()->getTorpedoCount() == 0) {
				    $this->getAttackShip()->setTorpedos(0);
                }
				$this->getAttackShip()->setEps($this->getAttackShip()->getEps() - $this->getProjectileWeaponEnergyCosts());
				$this->redefineDefender();
				$this->addMessage("Die ".$this->getAttackShip()->getName()." feuert einen ".$this->getAttackShip()->getTorpedo()->getName()." auf die ".$this->getDefendShip()->getName());
				// higher evade chance for pulseships against
				// torpedo ships
				if ($this->getAttackShip()->getRump()->getRoleId() == ShipRoleEnum::ROLE_TORPEDOSHIP && $this->getDefendShip()->getRump()->getRoleId() == ShipRoleEnum::ROLE_PULSESHIP) {
					$hitchance = round($this->getAttackShip()->getHitChance()*0.65);
				} else {
					$hitchance = $this->getAttackShip()->getHitChance();
				}
				if ($hitchance*(100-$this->getDefendShip()->getEvadeChance()) < rand(1,10000)) {
					$this->addMessage("Die ".$this->getDefendShip()->getName()." wurde verfehlt");
					continue;
				}
				$damage_wrapper = new DamageWrapper($this->getProjectileWeaponDamage($this->getAttackShip()),$this->getAttackShip()); {
					$damage_wrapper->setShieldDamageFactor($this->getAttackShip()->getTorpedo()->getShieldDamageFactor());
					$damage_wrapper->setHullDamageFactor($this->getAttackShip()->getTorpedo()->getHullDamageFactor());
					$damage_wrapper->setIsTorpedoDamage(TRUE);
				}
				$this->addMessageMerge($this->getDefendShip()->damage($damage_wrapper));
				if ($this->getDefendShip()->getIsDestroyed()) {
					$this->unsetDefender();

                    $this->getEntryCreator()->addShipEntry(
                        'Die '.$this->getDefendShip()->getName().' wurde in Sektor '.$this->getDefendShip()->getSectorString().' von der '.$this->getAttackShip()->getName().' zerstört',
                        (int) $this->getAttackShip()->getUserId()
                    );
                    $this->getShipRemover()->destroy($this->getDefendShip());
					break;
				}
			}
			$this->endCycle();
		}
	}

	/**
	 */
	private function endCycle(&$msg=array()) { #{{{
        // @todo refactor
        global $container;

        $shipRepo = $container->get(ShipRepositoryInterface::class);
        $this->addMessageMerge($msg);

        $shipRepo->save($this->getAttackShip());
		if ($this->getDefendShip()) {
		    $shipRepo->save($this->getDefendShip());
		}
	} # }}}

	/**
	 */
	private function redefineDefender() { #{{{
        // @todo refactor
        global $container;

        $shipRepo = $container->get(ShipRepositoryInterface::class);

        $shipRepo->save($this->getDefendShip());

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
			if ($arr->getIsDestroyed()) {
				return FALSE;
			}
			if ($arr->getDisabled()) {
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
			if ($obj->getIsDestroyed()) {
				unset($arr[$key]);
				continue;
			}
			if ($obj->getDisabled()) {
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
			if ($arr->getIsDestroyed()) {
				return FALSE;
			}
			if ($arr->getDisabled()) {
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
			if ($obj->getIsDestroyed() || $obj->getDisabled()) {
				unset($arr[$key]);
				continue;
			}
			if ($obj->getDisabled()) {
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

	private function canFire(ShipInterface $ship): bool {
		if ($ship->getEps() == 0) {
			return false;
		}
		if (!$ship->getNbs()) {
			return false;
		}
		if (!$ship->hasActiveWeapons()) {
			return false;
		}
		return true;
	}

    private function alertLevelBasedReaction(ShipInterface $ship): array {
        $msg = array();
        if ($ship->getCrewCount() == 0 || $ship->getRump()->isTrumfield()) {
            return $msg;
        }
        if ($ship->getAlertState() == ShipAlertStateEnum::ALERT_GREEN) {
            $ship->setAlertState(ShipAlertStateEnum::ALERT_YELLOW);
            $msg[] = "- Erhöhung der Alarmstufe wurde durchgeführt";
        }
        if ($ship->getDock()) {
            $ship->setDock(0);
            $msg[] = "- Das Schiff hat abgedockt";
        }
        if ($ship->getWarpState() == 1) {
            $ship->deactivateSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            $msg[] = "- Der Warpantrieb wurde deaktiviert";
        }
        if ($ship->getCloakState()) {
            $ship->deactivateSystem(ShipSystemTypeEnum::SYSTEM_CLOAK);
            $msg[] = "- Die Tarnung wurde deaktiviert";
        }
        if (!$ship->getShieldState() && !$ship->traktorBeamToShip() && $ship->systemIsActivateable(ShipSystemTypeEnum::SYSTEM_SHIELDS)) {
            if ($ship->isTraktorbeamActive()) {
                $ship->deactivateTraktorBeam();
                $msg[] = "- Der Traktorstrahl wurde deaktiviert";
            }
            $ship->activateSystem(ShipSystemTypeEnum::SYSTEM_SHIELDS);
            $msg[] = "- Die Schilde wurden aktiviert";
        }
        if ($ship->systemIsActivateable(ShipSystemTypeEnum::SYSTEM_NBS)) {
            $ship->activateSystem(ShipSystemTypeEnum::SYSTEM_NBS);
            $msg[] = "- Die Nahbereichssensoren wurden aktiviert";
        }
        if ($ship->getAlertState() >= ShipAlertStateEnum::ALERT_YELLOW) {
            if ($ship->systemIsActivateable(ShipSystemTypeEnum::SYSTEM_PHASER)) {
                $ship->activateSystem(ShipSystemTypeEnum::SYSTEM_PHASER);
                $msg[] = "- Die Strahlenwaffe wurde aktiviert";
            }
        }
        return $msg;
    }

    private function getEnergyWeaponDamage(ShipInterface $ship): float {
        if (!$ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)) {
            return 0;
        }
        $basedamage= calculateModuleValue($ship->getRump(),$ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->getModule(),'getBaseDamage');
        $variance = round($basedamage/100 * $this->getEnergyWeapon($ship)->getVariance());
        $damage = rand($basedamage-$variance,$basedamage+$variance);
        if (rand(1,100) <= $this->getEnergyWeapon($ship)->getCriticalChance()) {
            return $damage*2;
        }
        return $damage;
    }

    private function getProjectileWeaponDamage(ShipInterface $ship): float {
        $variance = round($ship->getTorpedo()->getBaseDamage()/100*$ship->getTorpedo()->getVariance());
        $basedamage= calculateModuleValue($ship->getRump(),$ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO)->getModule(),FALSE,$ship->getTorpedo()->getBaseDamage());
        $damage = rand($basedamage-$variance,$basedamage+$variance);
        if (rand(1,100) <= $ship->getTorpedo()->getCriticalChance()) {
            return $damage*2;
        }
        return $damage;
    }

    private function getEnergyWeapon(ShipInterface $ship): ?WeaponInterface {
        // @todo refactor
        global $container;

        return $container->get(WeaponRepositoryInterface::class)->findByModule(
            (int) $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->getModuleId()
        );
    }

    public function getProjectileWeaponEnergyCosts(): int {
        // @todo
        return 1;
    }

    public function getEnergyWeaponEnergyCosts(): int {
        // @todo
        return 1;
    }
}

class ShipSingleAttackCycle extends ShipAttackCycle {

	protected function isSingleMode() {
		return TRUE;
	}

}
