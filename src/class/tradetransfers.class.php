<?php

class TradeTransferData extends BaseTable {

	protected $tablename = 'stu_trade_transfers';
	const tablename = 'stu_trade_transfers';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getUserId() {
		return $this->data['user_id'];
	}

	public function setUserId($value) {
		$this->setFieldValue('user_id',$value,'getUserId');
	}

	public function getTradePostId() {
		return $this->data['posts_id'];
	}

	public function setTradePostId($value) {
		$this->setFieldValue('posts_id',$value,'getTradePostId');
	}

	public function getAmount() {
		return $this->data['count'];
	}

	public function setCount($value) {
		$this->setFieldValue('count',$value,'getAmount');
	}

	public function getDate() {
		return $this->data['date'];
	}

	public function setDate($value) {
		$this->setFieldValue('date',$value,'getDate');
	}
}
class TradeTransfer extends TradeTransferData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getSumByTradepost($postId,$userId) {
		return DB()->query("SELECT SUM(count) FROM ".self::tablename." WHERE posts_id=".intval($postId)." AND user_id=".intval($userId),1);
	}

}
