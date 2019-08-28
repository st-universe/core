<?php

class BuildingFunctionsData extends BaseTable {

	protected $tablename = 'stu_buildings_functions';
	const tablename = 'stu_buildings_functions';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getBuildingId() {
		return $this->data['buildings_id'];
	}

	public function setBuildingId($value) {
		$this->setFieldValue('buildings_id',$value,'getBuildingId');
	}

	public function getFunction() {
		return $this->data['function'];
	}

	public function setFunction($value) {
		$this->setFieldValue('function',$value,'getFunction');
	}

	/**
	 */
	static function getModuleFabOptions() { #{{{
		return array(BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL1,
			BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL2,
			BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL3,
			BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL1,
			BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL2,
			BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL3,
			BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL1,
			BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL2,
			BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL3,);
	} # }}}

	/**
	 */
	public function isModuleFab() { #{{{
		return in_array($this->getFunction(),self::getModuleFabOptions());
	} # }}}

	/**
	 */
	public function getShipyardOptions() { #{{{
		return array(
				BUILDING_FUNCTION_ESCORT_SHIPYARD,
				BUILDING_FUNCTION_FRIGATE_SHIPYARD,
				BUILDING_FUNCTION_CRUISER_SHIPYARD,
				BUILDING_FUNCTION_DESTROYER_SHIPYARD,
		       		);
	} # }}}

	/**
	 */
	public function isShipyard() { #{{{
		return in_array($this->getFunction(),self::getShipyardOptions());
	} # }}}

}
class BuildingFunctions extends BuildingFunctionsData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static public function getByBuilding($buildingId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE buildings_id=".intval($buildingId));
		return self::_getList($result,'BuildingFunctionsData','function');
	}
}
