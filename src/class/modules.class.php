<?php

class ModulesData extends BaseTable {

	const tablename = 'stu_modules';
	protected $tablename = 'stu_modules';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	public function getId() {
		return $this->data['id'];
	}

	public function getWarpcoreCapacity() {
		return $this->data['wkkap'];
	}

	public function setWarpcoreCapacity($value) {
		$this->setFieldValue('wkkap',$value,'getWarpcoreCapacity');
	}

	/**
	 */
	public function setName($value) { # {{{
		$this->setFieldValue('name',$value,'getName');
	} # }}}

	/**
	 */
	public function getName() { # {{{
		return $this->data['name'];
	} # }}}

	/**
	 */
	public function setLevel($value) { # {{{
		$this->setFieldValue('level',$value,'getLevel');
	} # }}}

	/**
	 */
	public function getLevel() { # {{{
		return $this->data['level'];
	} # }}}

	/**
	 */
	public function setUpgradeFactor($value) { # {{{
		$this->setFieldValue('upgrade_factor',$value,'getUpgradeFactor');
	} # }}}

	/**
	 */
	public function getUpgradeFactor() { # {{{
		return $this->data['upgrade_factor'];
	} # }}}

	/**
	 */
	public function setDowngradeFactor($value) { # {{{
		$this->setFieldValue('downgrade_factor',$value,'getDowngradeFactor');
	} # }}}

	/**
	 */
	public function getDowngradeFactor() { # {{{
		return $this->data['downgrade_factor'];
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
	public function setType($value) { # {{{
		$this->setFieldValue('type',$value,'getType');
	} # }}}

	/**
	 */
	public function getType() { # {{{
		return $this->data['type'];
	} # }}}

	/**
	 */
	public function getDescription() { #{{{
		return ModuleType::getDescription($this->getType());
	} # }}}

	/**
	 */
	public function setGoodId($value) { # {{{
		$this->setFieldValue('goods_id',$value,'getGoodId');
	} # }}}

	/**
	 */
	public function getGoodId() { # {{{
		return $this->data['goods_id'];
	} # }}}

	/**
	 */
	public function setViewable($value) { # {{{
		$this->setFieldValue('viewable',$value,'getViewable');
	} # }}}

	/**
	 */
	public function getViewable() { # {{{
		return $this->data['viewable'];
	} # }}}

	/**
	 */
	public function setRumpsRoleId($value) { # {{{
		$this->setFieldValue('rumps_role_id',$value,'getRumpsRoleId');
	} # }}}

	/**
	 */
	public function getRumpsRoleId() { # {{{
		return $this->data['rumps_role_id'];
	} # }}}
	
	/**
	 */
	public function getCost() { #{{{
		return ModuleCost::getByModule($this->getId());
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
	public function getQueueCount() { #{{{
		return new ModuleQueueColonyWrapper($this->getId());
	} # }}}

	/**
	 */
	public function hasSpecial($special_id) { #{{{
		return ModuleSpecial::countInstances($this->getId(),$special_id);
	} # }}}

	private $specials = NULL;

	/**
	 */
	public function getSpecials() { #{{{
		if ($this->specials === NULL) {
			$this->specials = ModuleSpecial::getBy('module_id='.$this->getId());
		}
		return $this->specials;
	} # }}}

}
class Modules extends ModulesData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	/**
	 */
	static function getByGoodId($id) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE goods_id=".intval($id)." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return new ModulesData($result);
	} # }}}

	/**
	 */
	static function getBy($qry) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE ".$qry);
		return self::_getList($result,'ModulesData','id','module');
	} # }}}

}
?>
