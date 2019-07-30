<?php

class ColStorageData extends BaseTable {

	protected $tablename = 'stu_colonies_storage';
	const tablename = 'stu_colonies_storage';

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
		return Good::getById($this->getGoodId());
	}

	public function lowerCount($count) {
		$this->setCount($this->data['count']-$count);
	}

	public function upperCount($count) {
		$this->setCount($this->data['count']+$count);
	}

	public function getColonyId() {
		return $this->data['colonies_id'];
	}

	public function setColonyId($value) {
		$this->setFieldValue('colonies_id',$value,'getColonyId');
	}
}

class ColStorage extends ColStorageData {

	static function getStorageBy($where) {
		$result = DB()->query("SELECT a.*,b.name FROM ".self::tablename." as a LEFT JOIN stu_goods as b ON b.id=a.goods_id WHERE ".$where." ORDER BY b.sort");
		return self::_getListAsArrayObject($result,'ColStorageData','goods_id');
	}

	static function truncate($colonyId=0) {
		DB()->query("DELETE FROM ".self::tablename." WHERE colonies_id=".intval($colonyId));
	}
}
?>
