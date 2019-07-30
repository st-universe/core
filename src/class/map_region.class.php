<?php
class MapRegionData extends BaseTable {

	protected $tablename = 'stu_map_regions';
	const tablename = 'stu_map_regions';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	public function getDescription() {
		return $this->data['description'];
	}

	public function setDescription($value) {
		$this->setFieldValue('description',$value,'getDescription');
	}

	public function getMapFieldTypes() {
		return MapFieldType::getList(' WHERE region_id='.$this->getId());
	}

	public function getDatabaseId() {
		return $this->data['database_id'];
	}

	public function setDatabaseId($value) {
		$this->setFieldValue('database_id',$value,'getDatabaseId');
	}
} 
class MapRegion extends MapRegionData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".$this->tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static public function getListBy($sql) {
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'MapfieldData');
	}

	static function getFieldsBy($sql) {
		return DB()->query("SELECT * FROM stu_map WHERE ".$sql." ORDER BY cy,cx");
	}

	static public function countInstances($sql) {
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." ".$sql,1);
	}
}
?>
