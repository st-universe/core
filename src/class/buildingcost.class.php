<?php

use Stu\Orm\Repository\CommodityRepositoryInterface;

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

	function getGood() {
		global $container;

		$commodityRepository = $container->get(CommodityRepositoryInterface::class);
		return $commodityRepository->find((int) $this->getGoodId());

	}

	function getAmount() {
		return $this->data['count'];
	}

	function getHalfAmount() {
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

}
?>
