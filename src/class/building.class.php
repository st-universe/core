<?php

class BuildingData extends BaseTable {

	const tablename = 'stu_buildings';
	protected $tablename = 'stu_buildings';

	function __construct($result) {
		$this->data = $result;
	}
	
	function getName() {
		return $this->data['name'];
	}

	function getBuildingType() {
		// return 0 for now
		return 0;
	}

	function getStorage() {
		return $this->data['lager'];
	}

	/**
	 */
	public function setEpsCost($value) { # {{{
		$this->setFieldValue('eps_cost',$value,'getEpsCost');
	} # }}}

	/**
	 */
	public function getEpsCost() { # {{{
		return $this->data['eps_cost'];
	} # }}}


	function getEpsStorage() {
		return $this->data['eps'];
	}

	function getEpsProduction() {
		return $this->data['eps_proc'];
	}

	function getEpsProductionDisplay() {
		if ($this->getEpsProduction() < 0) {
			return $this->getEpsProduction();
		}
		return '+'.$this->getEpsProduction();
	}

	public function getEpsProductionCss() {
		if ($this->getEpsProduction() < 0) {
			return 'negative';
		}
		if ($this->getEpsProduction() > 0) {
			return 'positive';
		}
	}

	function getHousing() {
		return $this->data['bev_pro'];
	}

	function getWorkers() {
		return $this->data['bev_use'];
	}

	function getIntegrity() {
		return $this->data['integrity'];
	}

	function getResearchId() {
		return $this->data['research_id'];
	}

	function isViewable() {
		return $this->data['view'];
	}

	function getShieldpoints() {
		return $this->data['schilde'];
	}

	function getConstructionTime() {
		return $this->data['buildtime'];
	}

	function getLimit() {
		return $this->data['blimit'];
	}

	function hasLimit() {
		return $this->getLimit() > 0;
	}

	function getLimitColony() {
		return $this->data['bclimit'];
	}

	function hasLimitColony() {
		return $this->getLimitColony() > 0;
	}

	function isActivateable() {
		return $this->data['is_activateable'];
	}

	function needsSpaceCenter() {
		return $this->data['needs_rbf'];
	}

	private $buildfields = NULL;

	function getBuildableFields() {
		if ($this->buildfields === NULL) {
			$this->buildfields = array();
			$result = DB()->query("SELECT type FROM stu_field_build WHERE buildings_id=".$this->getId());
			while ($field = mysqli_fetch_assoc($result)) {
				$this->buildfields[] = $field['type'];
			}
		}
		return $this->buildfields;
	}

	function getBuildTime() {
		return $this->data['buildtime'];
	}

	private $costs = NULL;

	/**
	 * @return BuildingCost[]
	 */
	function getCosts() {
		if ($this->costs === NULL) {
			$this->costs = BuildingCost::getCostByBuilding($this->getId());
		}
		return $this->costs;
	}

	private $goods = NULL;

	function getGoods() {
		if ($this->goods === NULL) {
			$this->goods = BuildingGood::getGoodsByBuilding($this->getId());
		}
		return $this->goods;
	}

	private $functions = NULL;

	public function getFunctions() {
		if ($this->functions === NULL) {
			$this->functions = BuildingFunctions::getByBuilding($this->getId());
		}
		return $this->functions;
	}

	public function hasFunction($func) {
		return array_key_exists($func,$this->getFunctions());
	}

	public function getFunction($func) {
		$arr = &$this->getFunctions();
		return $arr[$func];
	}

	public function isColonyCentral() {
		return $this->hasFunction(BUILDING_FUNCTION_CENTRAL);
	}

	public function isAcademy() {
		return $this->hasFunction(BUILDING_FUNCTION_ACADEMY);
	}

	public function isShipyard() {
		foreach (BuildingFunctions::getByBuilding($this->getId()) as $key => $func) {
			if ($func->isShipyard()) {
				return $func->getId();
			}
		}
		return FALSE;
	}

	/**
	 */
	public function postDeactivation(ColonyData $colony) { #{{{
		if (($func_id=$this->isShipyard())) {
			$func = new BuildingFunctions($func_id);
			ColonyShipQueue::stopBuildProcess($colony->getId(),$func->getFunction());
		}
	} # }}}

	/**
	 */
	public function postActivation(ColonyData $colony) { #{{{
		if (($func_id=$this->isShipyard())) {
			$func = new BuildingFunctions($func_id);
			ColonyShipQueue::restartBuildProcess($colony->getId(),$func->getFunction());
		}
	} # }}}

	/**
	 */
	public function isAirfield() { #{{{
		return $this->hasFunction(BUILDING_FUNCTION_AIRFIELD);
	} # }}}

	/**
	 */
	public function isFighterShipyard() { #{{{
		return $this->hasFunction(BUILDING_FUNCTION_FIGHTER_SHIPYARD);
	} # }}}

	/**
	 */
	public function isModuleFab() { #{{{
		foreach (BuildingFunctions::getByBuilding($this->getId()) as $key => $func) {
			if ($func->isModuleFab()) {
				return $func->getId();
			}
		}
		return FALSE;
	} # }}}

	/**
	 */
	public function isTorpedoFab() { #{{{
		return $this->hasFunction(BUILDING_FUNCTION_TORPEDO_FAB);
	} # }}}

	public function getFunctionString() {
		$func = array();
		if ($this->isShipyard()) {
			$func[] = "Schiffbau";
		}
		if ($this->isAcademy()) {
			$func[] = "Crewausbildung";
		}
		if ($this->isAirfield()) {
			$func[] = "Shuttlebau";
		}
		return implode(",",$func);
	}

	/**
	 */
	public function getFieldList() { #{{{
		return FieldBuilding::getObjectsBy('WHERE buildings_id='.$this->getId()." ORDER BY type");
	} # }}}

	/**
	 */
	public function isRemoveAble() { #{{{
		if ($this->isColonyCentral()) {
			return FALSE;
		}
		return TRUE;
	} # }}}

	/**
	 */
	public function onDestruction($colony_id) { #{{{
		// XXX we need a registry in here
		if ($this->isAcademy()) {
			CrewTraining::truncate('WHERE colony_id='.$colony_id);
		}
		if (($func_id=$this->isShipyard())) {
			$func = new BuildingFunctions($func_id);
			ColonyShipQueue::truncate('WHERE colony_id='.$colony_id.' AND building_function_id='.$func->getFunction());
		}
	} # }}}

}
class Building extends BuildingData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".intval($id)." LIMIT 1",4);
		if ($result == 0) {
			throw new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static public function getById($id) {
		return ResourceCache()->getObject('building',$id);
	}

	static function getBuildingMenuList($userId, $colonyId,$type,$offset=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE bm_col=".intval($type)." AND view=1 AND (research_id=0 OR research_id IN (SELECT research_id
			FROM stu_researched WHERE user_id=".$userId." AND aktiv=0)) AND id IN (SELECT buildings_id FROM stu_field_build WHERE type IN 
			(SELECT type FROM stu_colonies_fielddata WHERE colonies_id=".$colonyId.")) GROUP BY id ORDER BY name LIMIT ".$offset.",".BUILDMENU_SCROLLOFFSET);
		return self::_getList($result,'BuildingData','id','building');
	}

	/**
	 */
	static function getObjectsBy($sql) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'BuildingData');
	} # }}}

	/**
	 */
	static function cloneBuilding($buildingId,$newId) { #{{{
		if ($newId == 0) {
			return;
		}
		$result = DB()->query('SELECT * FROM '.self::tablename.' WHERE id='.$buildingId.' LIMIT 1',4);
		$result['id'] = $newId;
		$keys = array_keys($result);
		$values = array_values($result);
		DB()->query("INSERT INTO ".self::tablename." (".join(',',$keys).") VALUES ('".join('\',\'',$values)."')");
		
		$building = new Building($buildingId);
		foreach ($building->getCosts() as $key => $obj) {
			$cost = new BuildingCostData;
			$cost->setBuildingId($newId);
			$cost->setGoodId($obj->getGoodId());
			$cost->setCount($obj->getCount());
			$cost->save();
		}
		foreach ($building->getGoods() as $key => $obj) {
			$good = new BuildingGoodData;
			$good->setGoodId($obj->getGoodId());
			$good->setBuildingId($newId);
			$good->setCount($obj->getCount());
			$good->save();
		}
	} # }}}

}
?>
