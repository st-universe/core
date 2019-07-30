<?php

class ShipSystemsData extends BaseTable {

	const tablename = 'stu_ships_systems';
	protected $tablename = 'stu_ships_systems';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	public function getId() {
		return $this->data['id'];
	}

	public function getShipId() {
		return $this->data['ships_id'];
	}

	public function setShipId($value) {
		$this->setFieldValue('ships_id',$value,'getShipId');
	}

	public function getSystemType() {
		return $this->data['system_type'];
	}

	public function setSystemType($value) {
		$this->setFieldValue('system_type',$value,'getSystemType');
	}

	public function getModuleId() {
		return $this->data['module_id'];
	}

	public function setModuleId($value) {
		$this->setFieldValue('module_id',$value,'getModuleId');
	}

	public function getStatus() {
		return $this->data['status'];
	}

	public function setStatus($value) {
		$this->setFieldValue('status',$value,'getStatus');
	}

	public function isActivateable() {
		// XXX: TBD:
		return TRUE;
	}

	public function getEpsUsage() {
		// XXX: TDB:
		return 1;
	}

	/**
	 */
	public function isDisabled() { #{{{
		return $this->getStatus() == 0;
	} # }}}

	public function getShipField() {
		switch ($this->getSystemType()) {
			case SYSTEM_CLOAK:
				return 'cloak';
			case SYSTEM_NBS:
				return 'nbs';
			case SYSTEM_LSS:
				return 'lss';
			case SYSTEM_PHASER:
				return 'wea_phaser';
			case SYSTEM_TORPEDO:
				return 'wea_torp';
			case SYSTEM_WARPDRIVE:
				return 'warp';
			case SYSTEM_SHIELDS:
				return 'schilde_status';
		}
	}

	public function getDescription() {
		switch ($this->getSystemType()) {
			case SYSTEM_CLOAK:
				return "Tarnung";
			case SYSTEM_NBS:
				return "Nahbereichssensoren";
			case SYSTEM_LSS:
				return "Langstreckensensoren";
			case SYSTEM_PHASER:
				return "Strahlenwaffe";
			case SYSTEM_TORPEDO:
				return "Torpedobänke";
			case SYSTEM_WARPDRIVE:
				return "Warpantrieb";
			case SYSTEM_EPS:
				return _("Energiesystem");
			case SYSTEM_IMPULSEDRIVE:
				return _("Impulsantrieb");
			case SYSTEM_COMPUTER:
				return _('Computer');
			case SYSTEM_WARPCORE:
				return _('Warpkern');
			case SYSTEM_SHIELDS:
				return _('Schilde');
		}
	}

	public function getShipCallback() {
		switch ($this->getSystemType()) {
			case SYSTEM_CLOAK:
				return "setCloak";
			case SYSTEM_NBS:
				return "setNbs";
			case SYSTEM_LSS:
				return "setLss";
			case SYSTEM_PHASER:
				return "setPhaser";
			case SYSTEM_TORPEDO:
				return "setTorpedos";
			case SYSTEM_WARPDRIVE:
				return 'setWarpState';
			case SYSTEM_SHIELDS:
				return 'setShieldState';
		}
	}

	public function getModule() {
		return ResourceCache()->getObject('module',$this->getModuleId());
	}
}
class ShipSystems extends ShipSystemsData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static public function getByShip($shipId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE ships_id=".$shipId." ORDER BY system_type");
		return self::_getList($result,'ShipSystemsData','system_type'); 
	}

	static function truncate($shipId=0) {
		DB()->query("DELETE FROM ".self::tablename." WHERE ships_id=".intval($shipId));
	}

	/**
	 */
	static function createByModuleList($shipId,&$modules) { #{{{
		$systems = array();
		foreach ($modules as $key => $module) {
			switch ($module->getModule()->getType()) {
				case MODULE_TYPE_SHIELDS:
					$systems[SYSTEM_SHIELDS] = $module->getModule()->getId();
					break;
				case MODULE_TYPE_EPS:
					$systems[SYSTEM_EPS] = $module->getModule()->getId();
					break;
				case MODULE_TYPE_IMPULSEDRIVE:
					$systems[SYSTEM_IMPULSEDRIVE] = $module->getModule()->getId();
					break;
				case MODULE_TYPE_WARPCORE:
					$systems[SYSTEM_WARPCORE] = $module->getModule()->getId();
					$systems[SYSTEM_WARPDRIVE] = $module->getModule()->getId();
					break;
				case MODULE_TYPE_COMPUTER:
					$systems[SYSTEM_COMPUTER] = $module->getModule()->getId();
					$systems[SYSTEM_LSS] = 0;
					$systems[SYSTEM_NBS] = 0;
					break;
				case MODULE_TYPE_PHASER:
					$systems[SYSTEM_PHASER] = $module->getModule()->getId();
					break;
				case MODULE_TYPE_TORPEDO:
					$systems[SYSTEM_TORPEDO] = $module->getModule()->getId();
					break;
				case MODULE_TYPE_SPECIAL:
					// XXX: TBD
					break;
			}
		}
		foreach ($systems as $sysId => $moduleId) {
			$obj = new ShipSystemsData;
			$obj->setShipId($shipId);
			$obj->setSystemType($sysId);
			$obj->setModuleId($moduleId);
			$obj->setStatus(100);
			$obj->save();
		}
	} # }}}

}
?>
