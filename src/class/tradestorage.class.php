<?php

use Stu\Orm\Repository\TradePostRepositoryInterface;

class TradeStorageData extends BaseTable {

	protected $tablename = 'stu_trade_storage';
	const tablename = 'stu_trade_storage';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getUserId() {
		return $this->data['user_id'];
	}

	public function setUserId($value) {
		$this->setFieldValue('user_id',$value,'getUserId');
	}

	public function getUser() {
		return ResourceCache()->getObject("user",$this->getUserId());
	}

	public function getTradePostId() {
		return $this->data['posts_id'];
	}

	public function getTradePost() {
		// @todo refactor
		global $container;

		return $container->get(TradePostRepositoryInterface::class)->find((int) $this->getTradePostId());
	}

	public function setTradePostId($value) {
		$this->setFieldValue('posts_id',$value,'getTradePostId');
	}

	public function getGoodId() {
		return $this->data['goods_id'];
	}

	public function setGoodId($value) {
		$this->setFieldValue('goods_id',$value,'getGoodId');
	}
	
	public function getName() {
		return ResourceCache()->getObject('good',$this->getGoodId())->getName();
	}

	public function getAmount() {
		return $this->data['count'];
	}

	public function setCount($value) {
		$this->setFieldValue('count',$value,'getAmount');
	}

	public function upperCount($value) {
		$this->setCount($this->getAmount()+$value);
	}

	public function lowerCount($value) {
		$this->setCount($this->getAmount()-$value);
	}

	public function getGood() {
		return ResourceCache()->getObject('good',$this->getGoodId());
	}
}
class TradeStorage extends TradeStorageData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getStorageByUser($userId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE user_id=".$userId);
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			if (!array_key_exists($data['posts_id'],$ret)) {
				$ret[$data['posts_id']] = new \Stu\Lib\TradePostStorageWrapper($data['posts_id'],$userId);
			}
			$ret[$data['posts_id']]->addStorageEntry(new TradeStorageData($data));
		}
		return $ret;
	}

	static function getStorageByTradepostUser($postId,$userId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE posts_id=".intval($postId)." AND user_id=".intval($userId));
		$ret = new \Stu\Lib\TradePostStorageWrapper($postId,$userId);
		while($data = mysqli_fetch_assoc($result)) {
			$ret->addStorageEntry(new TradeStorageData($data));
		}
		return $ret;
	}

	static function getStorageByGood($postId,$userId,$goodId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE posts_id=".intval($postId)." AND user_id=".intval($userId)." AND goods_id=".intval($goodId)." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new TradeStorageData($result);
	}

	static function getAccountsByGood($goodId,$userId,$count=FALSE,$tradeNetwork=FALSE) {
		$tnqry = '';
		if ($tradeNetwork) {
			$tnqry = ' AND posts_id IN (SELECT id FROM stu_trade_posts WHERE trade_network='.intval($tradeNetwork).')';
		}
		$cntqry = '';
		if ($count) {
			$cntqry = ' AND count>='.intval($count);
		}
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE user_id=".intval($userId)." AND goods_id=".intval($goodId).$cntqry.$tnqry);
		return parent::_getList($result,'TradeStorageData');

	}

	static function getStorageSumBy($postId,$userId) {
		return DB()->query("SELECT SUM(count) FROM ".self::tablename." WHERE posts_id=".intval($postId)." AND user_id=".intval($userId),1);
	}
	
	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}
}

?>
