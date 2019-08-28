<?php

class BuildingFieldAlternativeData extends BaseTable {

	const tablename = 'stu_buildings_field_alternative';
	protected $tablename = 'stu_buildings_field_alternative';

	function __construct($result) {
		$this->data = $result;
	}
	
	public function getId() {
		return $this->data['buildings_id'];
	}

	public function getFieldType() {
		return $this->data['fieldtype'];
	}

	public function setFieldField($value) {
		$this->setFieldValue('fieldtype',$value,'getFieldType');
	}

	public function getBuildingId() {
		return $this->data['buildings_id'];
	}

	public function setBuildingId($value) {
		$this->setFieldValue('buildings_id',$value,'getBuildingsId');
	}

	public function getAlternativeBuildingId() {
		return $this->data['alternate_buildings_id'];
	}

	public function setAlternativeBuildingId($value) {
		$this->setFieldValue('alternate_buildings_id',$value,'getAlternativeBuildingId');
	}

	private $alternatebuilding = NULL;

	public function getAlternateBuilding() {
		if ($this->alternatebuilding === NULL) {
			$this->alternatebuilding = new Building($this->getAlternativeBuildingId());
		}
		return $this->alternatebuilding;
	}

	private $building = NULL;

	/**
	 */
	public function getBuilding() { #{{{
		if ($this->building === NULL) {
			$this->building = new Building($this->getBuildingId());
		}
		return $this->building;
	} # }}}

}
class BuildingFieldAlternative extends BuildingFieldAlternativeData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE buildings_id=".intval($id)." LIMIT 1",4);
		if ($result == 0) {
			throw new ObjectNotFoundException($id);
		}
		$this->data = &$result;
	}

	static public function getByBuildingField($buildingId,$fieldType) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE fieldtype=".intval($fieldType)." AND buildings_id=".intval($buildingId),4);	
		if ($result == 0) {
			return FALSE;
		}
		return new BuildingFieldAlternativeData($result);
	}

	/**
	 */
	static function getObjectsBy($sql) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'BuildingFieldAlternativeData');
	} # }}}

}
?>
