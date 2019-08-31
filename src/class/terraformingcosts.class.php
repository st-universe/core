<?php

use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

class TerraformingCostData extends BaseTable {

	protected $tablename = 'stu_terraforming_cost';
	const tablename = 'stu_terraforming_cost';
	
	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getTerraformingId() {
		return $this->data['terraforming_id'];
	}

	function getGoodsId() {
		return $this->data['goods_id'];
	}

	function getAmount() {
		return $this->data['count'];
	}

	public function getGood(): CommodityInterface
	{
		// @todo refactor
		global $container;

		return $container->get(CommodityRepositoryInterface::class)->find((int) $this->getGoodsId());
	}

}
class TerraformingCost extends TerraformingCostData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".$this->getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getByTerraforming($terraformingId=0) {
		$ret = array();
		$result = DB()->query("SELECT a.* FROM ".self::tablename." as a LEFT JOIN stu_goods as b ON b.id=a.goods_id WHERE a.terraforming_id=".intval($terraformingId)." ORDER BY b.sort");
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[$data['goods_id']] = new TerraformingCostData($data);
		}
		return $ret;
	}

}
?>
