<?php

class BuildingGoodData extends BaseTable {

	const tablename = 'stu_buildings_goods';
	protected $tablename = 'stu_buildings_goods';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function getId() {
		return $this->data['id'];
	}

	function getBuildingId() {
		return $this->data['buildings_id'];
	}

	function getGoodId() {
		return $this->data['goods_id'];
	}

	private $good = NULL;

	function getGood() {
		if ($this->good === NULL) {
			$this->good = new Good($this->getGoodId());
		}
		return $this->good;

	}

	function getCountDisplay() {
	    $value = $this->data['count'];
		if ($value < 0) {
			return $value;
		}
		return '+'.$value;
	}

	function getAmount() {
		return $this->data['count'];
	}

	function setBuildingId($value) {
		$this->data['buildings_id'] = $value;
		$this->addUpdateField('buildings_id','getBuildingId');
	}

	function setGoodId($value) {
		$this->data['goods_id'] = $value;
		$this->addUpdateField('goods_id','getGoodId');
	}

	function setAmount($value) {
		$this->data['count'] = $value;
		$this->addUpdateField('count','getAmount');
	}
}

class BuildingGood extends BuildingGoodData {

	static function getGoodsByBuilding($buildingId=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE buildings_id=".intval($buildingId));
		return self::_getList($result,'BuildingGoodData','goods_id');
	}

}
