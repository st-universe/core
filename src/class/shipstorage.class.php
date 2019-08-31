<?php
class ShipStorageData extends BaseTable {

	protected $tablename = 'stu_ships_storage';
	const tablename = 'stu_ships_storage';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	// Pseudo
	public function getName() {
		return $this->data['name'];
	}

	public function getGoodId() {
		return $this->data['goods_id'];
	}

	public function setGoodId($value) {
		$this->setFieldValue('goods_id',$value,'getGoodId');
	}

	public function getAmount() {
		return $this->data['count'];
	}

	public function setCount($value) {
		$this->setFieldValue('count',$value,'getAmount');
	}

	public function getGood() {
		return ResourceCache()->getObject("good",$this->getGoodId());
	}

	public function lowerCount($count) {
		$this->setCount($this->getAmount()-$count);
	}

	public function upperCount($count) {
		$this->setCount($this->getAmount()+$count);
	}

	public function getShipId() {
		return $this->data['ships_id'];
	}

	public function setShipId($value) {
		$this->setFieldValue('ships_id',$value,'getShipId');
	}

}

class ShipStorage extends ShipStorageData {

	static function getObjectsBy($where) {
		$result = DB()->query("SELECT a.*,b.name FROM ".self::tablename." as a LEFT JOIN stu_goods as b ON b.id=a.goods_id WHERE ".$where." ORDER BY b.sort");
		return self::_getListAsArrayObject($result,'ShipStorageData','goods_id');
	}

	static function truncate($shipId=0) {
		DB()->query("DELETE FROM ".self::tablename." WHERE ships_id=".intval($shipId));
	}
}
?>
