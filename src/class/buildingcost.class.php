<?php

class BuildingCostData extends BaseTable {
	
	protected $tablename = 'stu_buildings_cost';
	const tablename = 'stu_buildings_cost';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function getId() {
		return $this->data['id'];
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getBuildingId() {
		return $this->data['buildings_id'];
	}

	function getGoodId() {
		return $this->data['goods_id'];
	}

	public function getGoodsId() {
		return $this->getGoodId();
	}

	private $good = NULL;

	function getGood() {
		if ($this->good === NULL) {
			$this->good = new Good($this->getGoodId());
		}
		return $this->good;

	}

	function getAmount() {
		return $this->data['count'];
	}

	function getHalfCount() {
		return floor($this->getAmount()/2);
	}

	public function setTempCount($val) {
		$this->data['count'] = $val;
	}

	function setBuildingId($value) {
		$this->data['buildings_id'] = $value;
		$this->addUpdateField('buildings_id','getBuildingId');
	}

	function setGoodId($value) {
		$this->data['goods_id'] = $value;
		$this->addUpdateField('goods_id','getGoodId');
	}

	function setCount($value) {
		$this->data['count'] = $value;
		$this->addUpdateField('count','getCount');
	}

}
class BuildingCost extends BuildingCostData {

	static function getCostByBuilding($buildingId=0) {
		$ret = array();
		$result = DB()->query("SELECT * FROM stu_buildings_cost WHERE buildings_id=".intval($buildingId));
		while($data = mysqli_fetch_assoc($result)) {
			$ret[$data['goods_id']] = new BuildingCostData($data);
		}
		return $ret;
	}

	/**
	 */
	static function truncate($sql) { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}

}
?>
