<?php
class MapFieldTypeData extends BaseTable {

	const tablename = 'stu_map_ftypes';
	protected $tablename = 'stu_map_ftypes';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function getType() {
		return $this->data['type'];
	}

	public function setType($value) {
		$this->setFieldValue('type',$value,'getType');
	}

	function isSystem() {
		trigger_error('OBSOLETE isSystem - use getIsSystem');
		return $this->data['is_system'];
	}

	public function getIsSystem() {
		return $this->data['is_system'];
	}

	public function setIsSystem($value) {
		$this->setFieldValue('is_system',$value,'getIsSystem');
	}

	function getEpsCost() {
		return $this->data['ecost'];
	}

	public function setEpsCost($value) {
		$this->setFieldValue('ecost',$value,'getEpsCost');
	}

	public function getName() {
		return $this->data['name'];
	}

	public function setName($value) {
		$this->setFieldValue('name',$value,'getName');
	}

	public function getColonyClass() {
		return $this->data['colonies_classes_id'];
	}

	public function setColonyClass($value) {
		$this->setFieldValue('colonies_classes_id',$value,'getColonyClass');
	}

	public function getDamage() {
		return $this->data['damage'];
	}

	public function setDamage($value) {
		$this->setFieldValue('damage',$value,'getDamage');
	}

	public function getSpecialDamage() {
		return $this->data['x_damage'];
	}

	public function setSpecialDamage($value) {
		$this->setFieldValue('x_damage',$value,'getSpecialDamage');
	}

	/**
	 */
	public function setSpecialDamageInnerSystem($value) { # {{{
		$this->setFieldValue('x_damage_system',$value,'getSpecialDamageInnerSystem');
	} # }}}

	/**
	 */
	public function getSpecialDamageInnerSystem() { # {{{
		return $this->data['x_damage_system'];
	} # }}}
	
	public function getRegionId() {
		return $this->data['region_id'];
	}

	public function setRegionId($value) {
		$this->setFieldValue('region_id',$value,'getRegionId');
	}

	private $colonyclass = NULL;

	public function getColonyType() {
		if ($this->colonyclass === NULL) {
			$this->colonyclass = new ColonyClass($this->getColonyClass());
		}
		return $this->colonyclass;
	}

	public function getPassable() {
		return $this->data['passable'];
	}

	public function setPassable($value) {
		$this->setFieldValue('passable',$value,'getPassable');
	}

	public function isPassable() {
		return $this->getPassable() == 1;
	}
}
class MapFieldType extends MapFieldTypeData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getList($sql="") {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".self::tablename.$sql." ORDER BY id");
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[] = new MapFieldTypeData($data);
		}
		return $ret;
	}

	static function getSystemList() {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE is_system='1' ORDER BY id");
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[] = new MapFieldTypeData($data);
		}
		return $ret;
	}
	
	/**
	 */
	static function getFieldByType($typeId) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE type=".intval($typeId)." AND region_id=0 LIMIT 1",4);
		return new MapFieldTypeData($result);
	} # }}}
}
?>
