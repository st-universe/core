<?php

class ShiprumpData extends BaseTable {

	const tablename = 'stu_rumps';
	protected $tablename = 'stu_rumps';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function getDatabaseId() {
		return $this->data['database_id'];
	}

	function setDatabaseId($value) {
		$this->data['database_id'] = $value;
		$this->addUpdateField('database_id','getDatabaseId');
	}

	function getName() {
		return $this->data['name'];
	}

	public function getStorage() {
		return $this->data['storage'];
	}

	public function setStorage($value) {
		$this->setFieldValue('storage',$value,'getStorage');
	}

	/**
	 */
	public function isTrumfield() { #{{{
		return $this->getCategoryId() == SHIP_CATEGORY_DEBRISFIELD;
	} # }}}

	public function getDockingSlots() {
		return $this->data['slots'];
	}

	public function setDockingSlots($value) {
		$this->setFieldValue('slots',$value,'getDockingSlots');
	}

	/**
	 */
	public function setCategegoryId($value) { # {{{
		$this->setFieldValue('category_id',$value,'getCategegoryId');
	} # }}}

	/**
	 */
	public function getCategegoryId() { # {{{
		return $this->data['category_id'];
	} # }}}

	/**
	 */
	public function setEvadeChance($value) { # {{{
		$this->setFieldValue('evade_chance',$value,'getEvadeChance');
	} # }}}

	/**
	 */
	public function getEvadeChance() { # {{{
		return $this->data['evade_chance'];
	} # }}}

	/**
	 */
	public function setHitChance($value) { # {{{
		$this->setFieldValue('hit_chance',$value,'getHitChance');
	} # }}}

	/**
	 */
	public function getHitChance() { # {{{
		return $this->data['hit_chance'];
	} # }}}

	/**
	 */
	public function setModuleLevel($value) { # {{{
		$this->setFieldValue('module_level',$value,'getModuleLevel');
	} # }}}

	/**
	 */
	public function getModuleLevel() { # {{{
		return $this->data['module_level'];
	} # }}}

	private $module_levels = NULL;

	/**
	 */
	public function getModuleLevels() { #{{{
		if ($this->module_levels === NULL) {
			$this->module_levels = RumpModuleLevel::getByRump($this->getId());
		}
		return $this->module_levels;
	} # }}}

	/**
	 */
	public function setBaseCrew($value) { # {{{
		$this->setFieldValue('base_crew',$value,'getBaseCrew');
	} # }}}

	/**
	 */
	public function getBaseCrew() { # {{{
		return $this->data['base_crew'];
	} # }}}

	/**
	 */
	public function setBaseEps($value) { # {{{
		$this->setFieldValue('base_eps',$value,'getBaseEps');
	} # }}}

	/**
	 */
	public function getBaseEps() { # {{{
		return $this->data['base_eps'];
	} # }}}

	/**
	 */
	public function setBaseReactor($value) { # {{{
		$this->setFieldValue('base_reactor',$value,'getBaseReactor');
	} # }}}

	/**
	 */
	public function getBaseReactor() { # {{{
		return $this->data['base_reactor'];
	} # }}}

	/**
	 */
	public function setBaseHull($value) { # {{{
		$this->setFieldValue('base_hull',$value,'getBaseHull');
	} # }}}

	/**
	 */
	public function getBaseHull() { # {{{
		return $this->data['base_hull'];
	} # }}}

	/**
	 */
	public function setBaseShield($value) { # {{{
		$this->setFieldValue('base_shield',$value,'getBaseShield');
	} # }}}

	/**
	 */
	public function getBaseShield() { # {{{
		return $this->data['base_shield'];
	} # }}}

	/**
	 */
	public function setRoleId($value) { # {{{
		$this->setFieldValue('role_id',$value,'getRoleId');
	} # }}}

	/**
	 */
	public function getRoleId() { # {{{
		return $this->data['role_id'];
	} # }}}

	/**
	 */
	public function setBaseDamage($value) { # {{{
		$this->setFieldValue('base_damage',$value,'getBaseDamage');
	} # }}}

	/**
	 */
	public function getBaseDamage() { # {{{
		return $this->data['base_damage'];
	} # }}}

	
	/**
	 */
	public function enforceBuildableByUser($userId) { #{{{
		if (!array_key_exists($this->getId(),$this->getBuildableRumpsByUser($userId))) {
			throw new ObjectNotFoundException($this->getId());
		}
	} # }}}

	private $category = NULL;

	/**
	 */
	public function getCategory() { #{{{
		if ($this->category === NULL) {
			$this->category = new RumpsCategory($this->getCategoryId());
		}
		return $this->category;
	} # }}}

	/**
	 */
	public function setCategoryId($value) { # {{{
		$this->setFieldValue('category_id',$value,'getCategoryId');
	} # }}}

	/**
	 */
	public function getCategoryId() { # {{{
		return $this->data['category_id'];
	} # }}}

	/**
	 */
	public function setBaseSensorRange($value) { # {{{
		$this->setFieldValue('base_sensor_range',$value,'getBaseSensorRange');
	} # }}}

	/**
	 */
	public function getBaseSensorRange() { # {{{
		return $this->data['base_sensor_range'];
	} # }}}
	
	/**
	 */
	public function setTorpedoLevel($value) { # {{{
		$this->setFieldValue('torpedo_level',$value,'getTorpedoLevel');
	} # }}}

	/**
	 */
	public function getTorpedoLevel() { # {{{
		return $this->data['torpedo_level'];
	} # }}}

	/**
	 */
	public function setBaseTorpedoStorage($value) { # {{{
		$this->setFieldValue('base_torpedo_storage',$value,'getBaseTorpedoStorage');
	} # }}}

	/**
	 */
	public function getBaseTorpedoStorage() { # {{{
		return $this->data['base_torpedo_storage'];
	} # }}}

	/**
	 */
	public function setTorpedoVolleys($value) { # {{{
		$this->setFieldValue('torpedo_volleys',$value,'getTorpedoVolleys');
	} # }}}

	/**
	 */
	public function getTorpedoVolleys() { # {{{
		return $this->data['torpedo_volleys'];
	} # }}}

	/**
	 */
	public function getBuildplanCount() { #{{{
		return ShipBuildplans::countInstances('WHERE rump_id='.$this->getId().' AND user_id='.currentUser()->getId());
	} # }}}

	/**
	 */
	public function getShipCount() { #{{{
		return Ship::countInstances('WHERE rumps_id='.$this->getId().' AND user_id='.currentUser()->getId());
	} # }}}

	/**
	 */
	public function setPhaserVolleys($value) { # {{{
		$this->setFieldValue('phaser_volleys',$value,'getPhaserVolleys');
	} # }}}

	/**
	 */
	public function getPhaserVolleys() { # {{{
		return $this->data['phaser_volleys'];
	} # }}}

	/**
	 */
	public function setPhaserHullDamageFactor($value) { # {{{
		$this->setFieldValue('phaser_hull_damage_factor',$value,'getPhaserHullDamageFactor');
	} # }}}

	/**
	 */
	public function getPhaserHullDamageFactor() { # {{{
		return $this->data['phaser_hull_damage_factor'];
	} # }}}

	/**
	 */
	public function setPhaserShieldDamageFactor($value) { # {{{
		$this->setFieldValue('phaser_shield_damage_factor',$value,'getPhaserShieldDamageFactor');
	} # }}}

	/**
	 */
	public function getPhaserShieldDamageFactor() { # {{{
		return $this->data['phaser_shield_damage_factor'];
	} # }}}

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

	private $buildingsCosts = NULL;

	/**
	 * @return ShipRumpCosts[]
	 */
	public function getBuildingCosts() { #{{{
		if ($this->buildingsCosts === NULL) {
			$this->buildingsCosts = ShipRumpCosts::getByRump($this->getId());
		}
		return $this->buildingsCosts;
	} # }}}

	/**
	 */
	public function setIsNpc($value) { # {{{
		$this->setFieldValue('is_npc',$value,'getIsNpc');
	} # }}}

	/**
	 */
	public function getIsNpc() { # {{{
		return $this->data['is_npc'];
	} # }}}

	/**
	 */
	public function getRole() { #{{{
		return new RumpRole($this->getRoleId());
	} # }}}

	/**
	 */
	public function hasSpecial($value) { #{{{
		return RumpsSpecials::countInstances('WHERE rumps_id='.$this->getId().' AND special='.$value);
	} # }}}

	/**
	 */
	public function canColonize() { #{{{
		return $this->hasSpecial(RUMP_SPECIAL_COLONIZE);
	} # }}}

	/**
	 */
	public function setGoodId($value) { # {{{
		$this->setFieldValue('good_id',$value,'getGoodId');
	} # }}}

	/**
	 */
	public function getGoodId() { # {{{
		return $this->data['good_id'];
	} # }}}

	/**
	 */
	public function setEcost($value) { # {{{
		$this->setFieldValue('ecost',$value,'getEcost');
	} # }}}

	/**
	 */
	public function getEcost() { # {{{
		return $this->data['ecost'];
	} # }}}

	/**
	 */
	public function setBuildableAsGood($value) { # {{{
		$this->setFieldValue('buildable_as_good',$value,'getBuildableAsGood');
	} # }}}

	/**
	 */
	public function getBuildableAsGood() { # {{{
		return $this->data['buildable_as_good'];
	} # }}}

	/**
	 */
	public function getCrewSum() { #{{{
		return $this->getCategory()->getJob1Slots()+$this->getCategory()->getJob2Slots()+$this->getCategory()->getJob3Slots()+$this->getCategory()->getJob4Slots()+$this->getCategory()->getJob5Slots();
	} # }}}

	/**
	 */
	public function setFlightEcost($value) { # {{{
		$this->setFieldValue('flight_ecost',$value,'getFlightEcost');
	} # }}}

	/**
	 */
	public function getFlightEcost() { # {{{
		return $this->data['flight_ecost'];
	} # }}}
	
	/**
	 */
	private function getBaseCrewCount() { #{{{
		$count = 0;
		foreach(array(1,2,3,4,5,7) as $slot) {
			$crew_func = 'getJob'.$slot.'Crew';
			$count += $this->getCrewObj()->$crew_func();
		}
		return $count;
	} # }}}

	/**
	 */
	public function getCrew100P() { #{{{
		return $this->getBaseCrewCount()+$this->getCrewObj()->getJob6Crew();
	} # }}}

	/**
	 */
	public function getCrew110P() { #{{{
		return $this->getBaseCrewCount()+$this->getCrewObj()->getJob6Crew10p();
	} # }}}

	/**
	 */
	public function getCrew120P() { #{{{
		return $this->getBaseCrewCount()+$this->getCrewObj()->getJob6Crew20p();
	} # }}}

	private $crewobj = NULL;

	/**
	 */
	public function getCrewObj() { #{{{
		if ($this->crewobj === NULL) {
			$this->crewobj = RumpCatRoleCrew::getByRumpCatRole($this->getCategoryId(),$this->getRoleId());
		}
		return $this->crewobj;
	} # }}}

	/**
	 */
	public function setSort($value) { # {{{
		$this->setFieldValue('sort',$value,'getSort');
	} # }}}

	/**
	 */
	public function getSort() { # {{{
		return $this->data['sort'];
	} # }}}

}

class Shiprump extends ShiprumpData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	/**
	 */
	static function getBuildableRumpsByUser($user_id) { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' WHERE is_buildable=1 AND id IN (SELECT rump_id FROM stu_rumps_user WHERE user_id='.$user_id.')');
		return self::_getList($result,'ShipRumpData');
	} # }}}

	/**
	 */
	static function getBuildableRumpsByBuildingFunction($user_id,$function_id) { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' WHERE is_buildable=1 AND id IN (SELECT rump_id FROM stu_rumps_user WHERE user_id='.$user_id.') AND id IN (SELECT rump_id FROM stu_rumps_buildingfunction WHERE building_function='.$function_id.')');
		return self::_getList($result,'ShipRumpData');
	} # }}}

	/**
	 */
	static function getBy($sql) { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' '.$sql);
		return self::_getList($result,'ShipRumpData');
	} # }}}

}
?>
