<?php

class ColonyTickManager {

	private $colonies = NULL;
	private $tick = NULL;

	function __construct($tick) {
		DB()->beginTransaction();
		$this->colonies = Colony::getListBy('user_id IN (SELECT id FROM stu_user WHERE user_id!='.USER_NOONE.' AND tick='.intval($tick).')');
		//$this->colonies = Colony::getListBy('id=60');
		$this->tick = $tick;

		$this->setLock();
		$this->colonyLoop();
		$this->proceedCrewTraining();
		$this->repairShips();
		$this->clearLock();
		DB()->commitTransaction();
	}

	function getColonies() {
		return $this->colonies;
	}

	function colonyLoop() {
		foreach ($this->getColonies() as $key => $colony) {
			//echo "Processing Colony ".$colony->getId()." at ".microtime()."\n";
			new ColonyTick($colony);
		}
	}

	/**
	 */
	private function proceedCrewTraining() { #{{{
		$user = array();
		foreach (CrewTraining::getObjectsBy() as $obj) {
			if (!isset($user[$obj->getUserId()])) {
				$user[$obj->getUserId()] = 0;
			}
			if ($user[$obj->getUserId()] >= $obj->getUser()->getTrainableCrewCountMax()) {
				continue;
			}
			if ($obj->getUser()->getGlobalCrewLimit()-$obj->getUser()->getUsedCrewCount()-$obj->getUser()->getFreeCrewCount() <= 0) {
				continue;
			}
			if (!$obj->getColony()->hasActiveBuildingWithFunction(BUILDING_FUNCTION_ACADEMY)) {
				continue;
			}
			Crew::create($obj->getUserId());
			$obj->deleteFromDatabase();
			$user[$obj->getUserId()]++;
		}
	} # }}}

	/**
	 */
	private function repairShips() { #{{{
		foreach (ColonyShipRepair::getObjectsBy('id IN (SELECT MIN(id) FROM stu_colonies_shiprepair GROUP BY colony_id, field_id)') as $obj) {
			if (!$obj->getField()->isActive()) {
				continue;
			}
			$obj->getShip()->setHuell($obj->getShip()->getHuell()+$obj->getShip()->getRepairRate());
			if (!$obj->getShip()->canBeRepaired()) {
				$obj->getShip()->setHuell($obj->getShip()->getMaxHuell());
				$obj->getShip()->setState(SHIP_STATE_NONE);
				$obj->deleteFromDatabase();
			}
			$obj->getShip()->save();
		}
	} # }}}


	function getTick() {
		return $this->tick;
	}

	function setLock() {
		@touch(LOCKFILE_DIR.$this->getTick().'.lock');
	}

	function clearLock() {
		@unlink(LOCKFILE_DIR.$this->getTick().'.lock');
	}

}

class ColonyTick {

	private $colony = NULL;
	private $buildings = NULL;
	private $msg = array();

	function __construct(&$colony) {
		$this->colony = $colony;
		$this->mainLoop();
		$this->proceedStorage();
		$this->getColony()->save();
		$this->proceedModules();
		$this->sendMessages();
	}

	function getColony() {
		return $this->colony;
	}

	function mainLoop() {
		$i = 1;
		while(TRUE) {
			$rewind = 0;
			foreach($this->getColony()->getProductionRaw() as $key => $pro) {
				if ($pro->getProduction() >= 0) {
					continue;
				}
				if ($this->getColony()->getStorage()->offsetExists($pro->getGoodsId())) {
					if ($this->getColony()->getStorage()->offsetGet($pro->getGoodsId())->getCount() + $pro->getProduction() >= 0) {
						continue;
					}
				}
				$field = $this->getBuildingToDeactivateByGood($pro->getGoodsId());
				//echo $i." hit by good ".$field->getFieldId()." - produce ".$pro->getProduction()." MT ".microtime()."\n";
				$this->deactivateBuilding($field,$key);
				$rewind = 1;
			}
			if ($rewind == 0 && $this->getColony()->getEpsProduction() < 0 && $this->getColony()->getEps() + $this->getColony()->getEpsProduction() < 0) {
				$field = $this->getBuildingToDeactivateByEpsUsage();
				//echo $i." hit by eps ".$field->getFieldId()." - complete usage ".$this->getColony()->getEpsProduction()." - usage ".$field->getBuilding()->getEpsProduction()." MT ".microtime()."\n";
				$this->deactivateBuilding($field,0);
				$rewind = 1;
			}
			if ($rewind == 1) {
				reset($this->getColony()->getProductionRaw());
				$i++;
				if ($i == 100) {
					// SECURITY
					//echo "HIT SECURITY BREAK\n";
					break;
				}
				continue;
			}
			break;
		}
		$this->getColony()->setEps($this->getColony()->getEps()+$this->getColony()->getEpsProduction());
	}

	function deactivateBuilding(&$field,$key) {
		if ($key == 0) {
			$ext = "Energie";
		} else {
			$ext = getGoodName($key);
		}

		$this->addMessage($field->getBuilding()->getName()." auf Feld ".$field->getFieldId()." deaktiviert (Mangel an ".$ext.")");

		$this->getColony()->upperWorkless($field->getBuilding()->getWorkers());
		$this->getColony()->lowerWorkers($field->getBuilding()->getWorkers());
		$this->getColony()->lowerMaxBev($field->getBuilding()->getHousing());
		$this->getColony()->setEpsProduction($this->getColony()->getEpsProduction()-$field->getBuilding()->getEpsProduction());
		$this->mergeProduction($field->getBuilding()->getGoods());
		$field->getBuilding()->postDeactivation($this->getColony());

		$field->setActive(0);
		$field->save();
	}

	function getBuildingToDeactivateByGood($goodId) {
		return Colfields::getBy("colonies_id=".$this->getColony()->getId()." AND aktiv=1 AND buildings_id IN (SELECT buildings_id FROM stu_buildings_goods WHERE goods_id=".$goodId." AND count<0)");
	}

	function getBuildingToDeactivateByEpsUsage() {
		return Colfields::getBy("colonies_id=".$this->getColony()->getId()." AND aktiv=1 AND buildings_id IN (SELECT id FROM stu_buildings WHERE eps_proc<0)");
	}

	function proceedStorage() {
		$emigrated = 0;
		$production = $this->proceedFood();
		$sum = $this->getColony()->getStorageSum();
		foreach ($production as $key => $obj) {
			if ($obj->getProduction() >= 0) {
				continue;
			}
			if ($key == GoodData::NAHRUNG) {
				if (!$this->getColony()->getStorage()->offsetExists(GoodData::NAHRUNG) && $obj->getProduction() < 1) {
					$this->proceedEmigration(TRUE);
					$emigrated = 1;
				} elseif (($foodm=$this->getColony()->getStorage()->offsetGet(GoodData::NAHRUNG)->getAmount()+$obj->getProduction()) < 0) {
					$this->proceedEmigration(TRUE,abs($foodm));
					$emigrated = 1;
				}
			}
			$this->getColony()->lowerStorage($key,abs($obj->getProduction()));
			$sum -= abs($obj->getProduction());
		}
		foreach ($production as $key => $obj) {
			if ($obj->getProduction() <= 0 || !$obj->getGood()->isSaveable()) {
				continue;
			}
			if ($sum >= $this->getColony()->getMaxStorage()) {
				break;
			}
			if ($sum+$obj->getProduction() > $this->getColony()->getMaxStorage()) {
				$this->getColony()->upperStorage($key,$this->getColony()->getMaxStorage()-$sum);
				break;
			}
			$this->getColony()->upperStorage($key,$obj->getProduction());
			$sum += $obj->getProduction();
		}
		if ($this->getColony()->getUser()->getCurrentResearch() && $this->getColony()->getUser()->getCurrentResearch()->getActive()) {
			if (isset($production[$this->getColony()->getUser()->getCurrentResearch()->getResearch()->getGoodId()])) {
				$finished = $this->getColony()->getUser()->getCurrentResearch()->advance($production[$this->getColony()->getUser()->getCurrentResearch()->getResearch()->getGoodId()]->getProduction());
			}
		}
		if ($this->getColony()->hasOverpopulation()) {
			$this->proceedEmigration();
			return;
		}
		if ($this->getColony()->getPopulationLimit() > 0 && $this->getColony()->getPopulation() > $this->getColony()->getPopulationLimit() && $this->getColony()->getWorkless()) {
			if (($free=($this->getColony()->getPopulationLimit()-$this->getColony()->getWorkers())) > 0) {
				$this->addMessage(sprintf(_('Es sind %d Arbeitslose ausgewandert'),($this->getColony()->getWorkless()-$free)));
				$this->getColony()->setWorkless($free);
			} else {
				$this->addMessage(_('Es sind alle Arbeitslosen ausgewandert'));
				$this->getColony()->setWorkless(0);
			}
		}
		if ($emigrated == 0) {
			$this->proceedImmigration();
		}
	}

	/**
	 */
	private function proceedModules() { #{{{
		foreach (ModuleQueue::getObjectsBy('WHERE colony_id='.$this->getColony()->getId()) as $id => $queue) {
			if ($this->getColony()->hasActiveBuildingWithFunction($queue->getBuildingFunction())) {
				$this->getColony()->upperStorage($queue->getModule()->getGoodId(),$queue->getCount());
				$this->addMessage(sprintf(_('Es wurden %d %s hergestellt'),$queue->getCount(),$queue->getModule()->getName()));
				$queue->deleteFromDatabase();
			}
		}
	} # }}}

	function proceedFood() {
		$foodvalue = $this->getColony()->getBevFood();
		$prod = &$this->getColony()->getProductionRaw();
		if (!array_key_exists(GoodData::NAHRUNG,$prod)) {
			$obj = new ColProductionData;
			$obj->setGoodId(GoodData::NAHRUNG);
			$obj->lowerProduction($foodvalue);
			$prod[GoodData::NAHRUNG] = $obj;
		} else {
			$prod[GoodData::NAHRUNG]->lowerProduction($foodvalue);
		}
		return $prod;
	}

	function proceedImmigration() {
		// XXX	
		$im = $this->getColony()->getImmigration();
		$this->getColony()->upperWorkless($im);
	}

	private function proceedEmigration($foodrelated=FALSE,$foodmissing=FALSE) {
		if ($this->getColony()->getWorkless()) {
			if ($foodmissing > 0) {
				$bev = $foodmissing*PEOPLE_FOOD;
				if ($bev > $this->getColony()->getWorkless()) {
					$bev = $this->getColony()->getWorkless();
				}
			} else {
				if ($foodrelated) {
					$bev = $this->getColony()->getWorkless();
				} else {
					$bev = rand(1,$this->getColony()->getWorkless());
				}
			}
			$this->getColony()->lowerWorkless($bev);
			if ($foodrelated) {
				$this->addMessage($bev." Einwohner sind aufgrund des Nahrungsmangels ausgewandert");
			} else {
				$this->addMessage($bev." Einwohner sind ausgewandert");
			}
		}
	}

	function addMessage($msg) {
		$this->msg[] = $msg;
	}

	function getMessages() {
		return $this->msg;
	}

	function sendMessages() {
		if (count($this->getMessages()) == 0) {
			return;
		}
		$text = "Tickreport der Kolonie ".$this->getColony()->getNameWithoutMarkup()."\n";
		foreach ($this->getMessages() as $key => $msg) {
			$text .= $msg."\n";
		}
		PM::sendPM(USER_NOONE,$this->getColony()->getUserId(),$text,PM_SPECIAL_COLONY);
	}

	function mergeProduction(&$arr) {
		$prod = $this->getColony()->getProductionRaw();
		foreach ($arr as $key => $obj) {
			if (!array_key_exists($key,$prod)) {
				$data = new ColProductionData;
				$data->setGoodId($key);	
				$data->setProduction($obj->getCount()*-1);
				$this->getColony()->setProductionRaw($this->getColony()->getProductionRaw()+array($key => $data));
			} else {
				if ($obj->getCount() < 0) {
					$prod[$key]->upperProduction(abs($obj->getCount()));
				} else {
					$prod[$key]->lowerProduction($obj->getCount());
				}
			}
		}
	}

}

?>
