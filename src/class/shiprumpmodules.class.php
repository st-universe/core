<?php
class ShipRumpModulesData extends BaseTable {

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	public function getRumpId() {
		return $this->data['rumps_id'];
	}

	public function setRumpId($value) {
		$this->setFieldValue('rumps_id',$value,'getRumpId');
	}

	public function getModuleType() {
		return $this->data['module_type'];
	}

	public function setModuleType($value) {
		$this->setFieldValue('module_type',$value,'getModuleType');
	}

	public function getMinLevel() {
		return $this->data['min_level'];
	}

	public function setMinLevel($value) {
		$this->setFieldValue('min_level',$value,'getMinLevel');
	}

	public function getMaxLevel() {
		return $this->data['max_level'];
	}

	public function setMaxLevel($value) {
		$this->setFieldValue('max_level',$value,'getMaxLevel');
	}
	
	public function getAmount() {
		return $this->data['amount'];
	}

	public function setAmount($value) {
		$this->setFieldValue('amount',$value,'getAmount');
	}

}
class ShipRumpModules extends ShipRumpModulesData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		return self::_getById($result,$id,'ShipRumpModulesData');	
	}

	static public function getByType($rumpId,$type) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE rumps_id=".intval($rumpId)." AND module_type=".intval($type)." LIMIT 1",4);
		return self::_getById($result,$rumpId,'ShipRumpModulesData');
	}

	static public function getByRump($rumpId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE rumps_id=".intval($rumpId);
		return self::_getList($result,'ShipRumpModulesData','module_type');
	}

}
?>
