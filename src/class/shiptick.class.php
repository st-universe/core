<?php

class ShipTickManager {

	private $ships = NULL;

	function __construct() {
		$this->ships = Ship::getObjectsBy('WHERE user_id IN (SELECT id FROM stu_user WHERE id!='.USER_NOONE.' AND npc_type IS NULL)');
		//$this->ships = Ship::getObjectsBy('WHERE id=608');

		DB()->beginTransaction();
		$this->loop();
		$this->handleNPCShips();
		$this->lowerTrumfieldHuell();
		DB()->commitTransaction();
	}

	private function getShips() {
		return $this->ships;
	}

	private function loop() {
		foreach ($this->getShips() as $key => $ship) {
			//echo "Processing Ship ".$ship->getId()." at ".microtime()."\n";
			new ShipTick($ship);
		}
	}

	/**
	 */
	private function lowerTrumfieldHuell() { #{{{
		foreach (Ship::getObjectsBy('WHERE user_id='.USER_NOONE.' AND rumps_id IN (SELECT id FROM stu_rumps WHERE category_id='.SHIP_CATEGORY_DEBRISFIELD.')') as $key => $ship) {
			$lower = rand(5,15);
			if ($ship->getHuell() <= $lower) {
				$ship->destroyTrumfield();
				$ship->save();
				continue;
			}
			$ship->lowerHuell($lower);
			$ship->save();
		}
	} # }}}

	/**
	 */
	private function handleNPCShips() { #{{{
		foreach (Ship::getObjectsBy('WHERE user_id IN (SELECT id FROM stu_user where id!='.USER_NOONE.' AND npc_type IS NOT NULL)') as $key => $ship) {
			$eps = ceil($ship->getMaxEps()/10);
			if ($eps + $ship->getEps() > $ship->getMaxEps()) {
				$eps = $ship->getMaxEps()-$ship->getEps();
			}
			$ship->upperEps($eps);
			$ship->save();
		}
	} # }}}

}

class ShipTick {

	private $ship = NULL;
	private $msg = array();

	function __construct(&$ship) {
		$this->ship = &$ship;
		$this->handle();
		$this->finish();
		$this->sendMessages();
	}

	private function getShip() {
		return $this->ship;
	}

	private function handle() {
		if ($this->getShip()->getCrewCount() < $this->getShip()->getBuildplan()->getCrew()) {
			return;
		}
		$eps = $this->getShip()->getEps()+$this->getShip()->getEpsProduction();
		if ($this->getShip()->getEpsUsage() > $eps) {
			foreach ($this->getShip()->getActiveSystems() as $key => $system) {
				//echo "- eps: ".$eps." - usage: ".$this->getShip()->getEpsUsage()."\n";
				if ($eps - $this->getShip()->getEpsUsage() - $system->getEpsUsage() < 0) {
					//echo "-- hit system: ".$system->getDescription()."\n";
					$cb = $system->getShipCallback();
					$this->getShip()->$cb(0);
					$this->getShip()->lowerEpsUsage($system->getEpsUsage());
					$this->addMessage($system->getDescription().' deaktiviert wegen Energiemangel');
				}
				if ($this->getShip()->getEpsUsage() <= $eps) {
					break;
				}
			}
		}
		$eps -= $this->getShip()->getEpsUsage();
		if ($eps > $this->getShip()->getMaxEps()) {
			$eps = $this->getShip()->getMaxEps();
		}
		$wkuse = $this->getShip()->getEpsUsage() + ($eps-$this->getShip()->getEps());
		//echo "--- Generated Id ".$this->getShip()->getId()." - eps: ".$eps." - usage: ".$this->getShip()->getEpsUsage()." - old eps: ".$this->getShip()->getEps()." - wk: ".$wkuse."\n";
		$this->getShip()->setEps($eps);
		$this->getShip()->lowerWarpcoreLoad($wkuse);
	}

	private function finish() {
		$this->getShip()->save();
	}

	private function addMessage($msg) {
		$this->msg[] = $msg;
	}

	private function getMessages() {
		return $this->msg;
	}

	private function sendMessages() {
		if (count($this->getMessages()) == 0) {
			return;
		}
		$text = "Tickreport der ".$this->getShip()->getName()."\n";
		foreach ($this->getMessages() as $key => $msg) {
			$text .= $msg."\n";
		}
		PM::sendPM(USER_NOONE,$this->getShip()->getUserId(),$text,PM_SPECIAL_SHIP);
	}

}

?>
