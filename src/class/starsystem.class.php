<?php


class StarSystemData extends BaseTable {

	protected $tablename = 'stu_systems';
	const tablename = 'stu_systems';

	function __construct($data=array()) {
		$this->data = $data; 
	}

	function getName() {
		return $this->data['name'];
	}

	function getType() {
		return $this->data['type'];
	}

	/**
	 */
	public function setCX($value) { # {{{
		$this->setFieldValue('cx',$value,'getCX');
	} # }}}

	/**
	 */
	public function getCX() { # {{{
		return $this->data['cx'];
	} # }}}

	/**
	 */
	public function setCY($value) { # {{{
		$this->setFieldValue('cy',$value,'getCY');
	} # }}}

	/**
	 */
	public function getCY() { # {{{
		return $this->data['cy'];
	} # }}}
	
	/**
	 */
	public function setMaxX($value) { # {{{
		$this->setFieldValue('max_x',$value,'getMaxX');
	} # }}}

	/**
	 */
	public function getMaxX() { # {{{
		return $this->data['max_x'];
	} # }}}

	/**
	 */
	public function setMaxY($value) { # {{{
		$this->setFieldValue('max_y',$value,'getMaxY');
	} # }}}

	/**
	 */
	public function getMaxY() { # {{{
		return $this->data['max_y'];
	} # }}}

	
	function setType($value) {
		$this->data['type'] = $value;
		$this->addUpdateField('type','getType');
	}

	function setName($value) {
		$this->data['name'] = $value;
		$this->addUpdateField('name','getName');
	}

	/**
	 */
	public function setBonusFields($value) { # {{{
		$this->setFieldValue('bonus_fields',$value,'getBonusFields');
	} # }}}

	/**
	 */
	public function getBonusFields() { # {{{
		return $this->data['bonus_fields'];
	} # }}}

	private $systemType = NULL;

	/**
	 */
	public function getSystemType() { #{{{
		if ($this->systemType === NULL) {
			$this->systemType = new SystemType($this->getType());
		}
		return $this->systemType;
	} # }}}

	/**
	 */
	public function setDatabaseId($value) { # {{{
		$this->setFieldValue('database_id',$value,'getDatabaseId');
	} # }}}

	/**
	 */
	public function getDatabaseId() { # {{{
		return $this->data['database_id'];
	} # }}}
	
	private $fields = NULL;

	/**
	 */
	public function getFields() { #{{{
		if ($this->fields === NULL) {
			$this->fields = SystemMap::getObjectsBy('WHERE systems_id='.$this->getId().' ORDER BY sy,sx');
		}
		return $this->fields;
	} # }}}

	/**
	 */
	public function getFieldsMapped() { #{{{
		$fields = array();
		foreach ($this->getFields() as $key => $obj) {
			$fields['fields'][$obj->getSY()][] = $obj;
		}
		$fields['xaxis'] = range(1,$this->getMaxX());
		return $fields;
	} # }}}

}

class StarSystem extends StarSystemData {
	
	function __construct(&$id) {
		$result = DB()->query("SELECT * FROM ".parent::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		parent::__construct($result);
	}
	
	static function getSystemByCoords(&$cx,&$cy) {
		$result = DB()->query("SELECT * FROM ".parent::tablename." WHERE cx=".$cx." AND cy=".$cy." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new StarSystemData($result);
	}

	/**
	 */
	static function getObjectsBy($sql) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'StarSystemData');
	} # }}}

	static function getList() {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".parent::tablename." ORDER BY name");
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[] = new StarSystemData($data);
		}
		return $ret;
	}
}
?>
