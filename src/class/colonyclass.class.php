<?php


class ColonyClassData extends BaseTable {

	protected $tablename = 'stu_colonies_classes';
	const tablename = 'stu_colonies_classes';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getName() {
		return $this->data['name'];
	}

	public function setName($value) {
		$this->setFieldValue('name',$value,'getName');
	}

	public function getDatabaseId() {
		return $this->data['database_id'];
	}

	public function setDatabaseId($value) {
		$this->setFieldValue('database_id',$value,'getDatabaseId');
	}

	/**
	 */
	public function setColonizeableFields($value) { # {{{
		$this->setFieldValue('colonizeable_fields',$value,'getColonizeableFields');
	} # }}}

	/**
	 */
	public function getColonizeableFields() { # {{{
		return $this->data['colonizeable_fields'];
	} # }}}

	private $colonizeableFieldArray = NULL;
	/**
	 */
	public function getColonizeableFieldsAsArray() { #{{{
		if ($this->colonizeableFieldArray === NULL) {
			$this->colonizeableFieldArray = explode(",",$this->getColonizeableFields());
		}
		return $this->colonizeableFieldArray;
	} # }}}

	/**
	 */
	public function hasColonizeableFields() { #{{{
		return $this->getColonizeableFields() != '';
	} # }}}

	/**
	 */
	public function setIsMoon($value) { # {{{
		$this->setFieldValue('is_moon',$value,'getIsMoon');
	} # }}}

	/**
	 */
	public function getIsMoon() { # {{{
		return $this->data['is_moon'];
	} # }}}
	
	/**
	 */
	public function setBevGrowthRate($value) { # {{{
		$this->setFieldValue('bev_growth_rate',$value,'getBevGrowthRate');
	} # }}}

	/**
	 */
	public function getBevGrowthRate() { # {{{
		return $this->data['bev_growth_rate'];
	} # }}}

	/**
	 */
	public function setSpecial($value) { # {{{
		$this->setFieldValue('special',$value,'getSpecial');
	} # }}}

	/**
	 */
	public function getSpecial() { # {{{
		return $this->data['special'];
	} # }}}

	/**
	 */
	public function hasRing() { #{{{
		return $this->getSpecial() == COLONY_CLASS_SPECIAL_RING;
	} # }}}

	/**
	 */
	public function setAllowStart($value) { # {{{
		$this->setFieldValue('allow_start',$value,'getAllowStart');
	} # }}}

	/**
	 */
	public function getAllowStart() { # {{{
		return $this->data['allow_start'];
	} # }}}

	public function setResearchId($value) {
		$this->setFieldValue('research_id',$value,'getResearchId');
	}

	public function getResearchId() {
		return $this->data['research_id'];
	}
}
class ColonyClass extends ColonyClassData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	/**
	 */
	static function getObjectsBy($sql="") { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'ColonyClassData');
	} # }}}

}
?>
