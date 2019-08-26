<?php

class ShipBuildplansData extends BaseTable {

	protected $tablename = 'stu_buildplans';
	const tablename = 'stu_buildplans';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	public function getName() {
		return $this->data['name'];
	}

	public function setName($value) {
		$this->setFieldValue('name',$value,'getName');
	}

	/**
	 */
	static public function createSignature($modules) { #{{{
		return md5(implode('_',$modules));
	} # }}}

	/**
	 */
	public function setBuildtime($value) { # {{{
		$this->setFieldValue('buildtime',$value,'getBuildtime');
	} # }}}

	/**
	 */
	public function getBuildtime() { # {{{
		return $this->data['buildtime'];
	} # }}}

	/**
	 */
	public function setUserId($value) { # {{{
		$this->setFieldValue('user_id',$value,'getUserId');
	} # }}}

	/**
	 */
	public function getUserId() { # {{{
		return $this->data['user_id'];
	} # }}}

	/**
	 */
	public function setRumpId($value) { # {{{
		$this->setFieldValue('rump_id',$value,'getRumpId');
	} # }}}

	/**
	 */
	public function getRumpId() { # {{{
		return $this->data['rump_id'];
	} # }}}

	/**
	 */
	function getRump() { #{{{
		if ($this->rump === NULL) {
			$this->rump = new Shiprump($this->getRumpId());
		}
		return $this->rump;
	} # }}}

	/**
	 */
	public function setSignature($value) { # {{{
		$this->setFieldValue('signature',$value,'getSignature');
	} # }}}

	/**
	 */
	public function getSignature() { # {{{
		return $this->data['signature'];
	} # }}}

	/**
	 */
	public function getModulesByType($type) { #{{{
		return BuildPlanModules::getByType($this->getId(),$type);
	} # }}}

	/**
	 */
	public function setCrew($value) { # {{{
		$this->setFieldValue('crew',$value,'getCrew');
	} # }}}

	/**
	 */
	public function getCrew() { # {{{
		return $this->data['crew'];
	} # }}}

	/**
	 */
	public function setCrewPercentage($value) { # {{{
		$this->setFieldValue('crew_percentage',$value,'getCrewPercentage');
	} # }}}

	/**
	 */
	public function getCrewPercentage() { # {{{
		return $this->data['crew_percentage'];
	} # }}}
	
	private $modules = NULL;

	/**
	 */
	public function getModules() { #{{{
		if ($this->modules === NULL) {
			$this->modules = BuildPlanModules::getByBuildplan($this->getId());
		}
		return $this->modules;
	} # }}}

	/**
	 */
	public function getBuildtimeFormatted() { #{{{
		return formatSeconds($this->getBuildtime());
	} # }}}

	/**
	 */
	public function isDeleteAble() { #{{{
		return Ship::countInstances('WHERE plans_id='.$this->getId()) == 0 && ColonyShipQueue::countInstances('WHERE buildplan_id='.$this->getId()) == 0;
	} # }}}

	/**
	 */
	public function ownedByCurrentUser() { #{{{
		return $this->getUserId() == currentUser()->getId();
	} # }}}

	/**
	 */
	public function delete() { #{{{
		if (!$this->isDeleteAble()) {
			return;
		}
		if (!$this->ownedByCurrentUser()) {
			return;
		}
		$this->deleteFromDatabase();
	} # }}}

	/**
	 */
	public function getModule() { #{{{
		return new ModuleSelectWrapper($this);
	} # }}}

	/**
	 */
	public function deepDelete() { #{{{
		foreach ($this->getModules() as $key => $obj) {
			$obj->deepDelete();
		}
		$this->deleteFromDatabase();
	} # }}}

}

class ShipBuildplans extends ShipBuildplansData {
	
	function __construct(&$planId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".intval($planId),4);
		if ($result == 0) {
			throw new ObjectNotFoundException($planId);
		}
		parent::__construct($result);
	}

	static function getObjectsBy($where) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE ".$where);
		return self::_getList($result,'ShipBuildplansData');
	}

	/**
	 */
	static function getBySignature($userId,&$signature) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE user_id=".intval($userId)." AND signature='".$signature."'",4);
		if ($result == 0) {
			return FALSE;
		}
		return new ShipBuildplansData($result);
	} # }}}

	/**
	 */
	static function countInstances($sql) { #{{{
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." ".$sql,1);
	} # }}}

	/**
	 */
	static function getBuildplansByUserAndFunction($user_id,$function_id) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE user_id=".$user_id." AND rump_id IN (SELECT rump_id FROM stu_rumps_buildingfunction WHERE building_function=".$function_id.") ORDER BY name");
		return self::_getList($result,'ShipBuildPlansData');
	} # }}}

}
?>
