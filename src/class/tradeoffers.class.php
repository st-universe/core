<?php

use Stu\Orm\Repository\TradeLicenseRepositoryInterface;

class TradeOfferData extends BaseTable {

	protected $tablename = 'stu_trade_offers';
	const tablename = 'stu_trade_offers';

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
		return ResourceCache()->getObject('tradepost',$this->getTradePostId());
	}

	public function setTradePostId($value) {
		$this->setFieldValue('posts_id',$value,'getTradePostId');
	}

	public function getOfferCount() {
		return $this->data['amount'];
	}

	public function setOfferCount($value) {
		$this->setFieldValue('amount',$value,'getOfferCount');
	}

	public function upperOfferCount($value) {
		$this->setOfferCount($this->getOfferCount()+$value);
	}

	public function lowerOfferCount($value) {
		$this->setOfferCount($this->getOfferCount()-$value);
	}

	public function getOfferedGoodId() {
		return $this->data['gg_id'];
	}

	public function setOfferedGoodId($value) {
		$this->setFieldValue('gg_id',$value,'getOfferedGoodId');
	}

	public function getOfferedGoodObject() {
		return ResourceCache()->getObject('good',$this->getOfferedGoodId());
	}

	public function getOfferedGoodCount() {
		return $this->data['gg_count'];
	}

	public function setOfferedGoodCount($value) {
		$this->setFieldValue('gg_count',$value,'getOfferedGoodCount');
	}

	public function getWantedGoodId() {
		return $this->data['wg_id'];
	}

	public function getWantedGoodObject() {
		return ResourceCache()->getObject('good',$this->getWantedGoodId());
	}

	public function setWantedGoodId($value) {
		$this->setFieldValue('wg_id',$value,'getWantedGoodId');
	}

	public function getWantedGoodCount() {
		return $this->data['wg_count'];
	}

	public function setWantedGoodCount($value) {
		$this->setFieldValue('wg_count',$value,'getWantedGoodCount');
	}

	public function getDate() {
		return $this->data['date'];
	}

	public function setDate($value) {
		$this->setFieldValue('date',$value,'getDate');
	}

	public function getCacheValue() {
		return $this->getId()."_".$this->getOfferCount();
	}
}
class TradeOffer extends TradeOfferData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getByLicencedTradePosts($userId) {
		// @todo refactor
		global $container;
		$list = $container->get(TradeLicenseRepositoryInterface::class)->getByUser((int) $userId);
		$ret = array();
		$ret[] = 0;
		foreach($list as $key => $value) {
			$ret[] = $value->getTradePostId();
		}
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE posts_id IN (".implode(",",$ret).") ORDER BY date DESC");
		return self::_getList($result,'TradeOfferData');
	}
	
	static function getStorageByTradepostUser($postId,$userId) {
		$result = DB()->query("SELECT id,posts_id,gg_id as goods_id,SUM(gg_count*amount) as count,user_id FROM ".self::tablename." WHERE posts_id=".intval($postId)." AND user_id=".intval($userId)." GROUP BY gg_id");
		return self::_getList($result,'TradeStorageData','goods_id');
	}

	static function getOfferSumBy($postId,$userId) {
		return DB()->query("SELECT SUM(gg_count*amount) FROM ".self::tablename." WHERE posts_id=".intval($postId)." AND user_id=".intval($userId),1);
	}

	static function getOfferByGood($postId,$userId,$goodId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE posts_id=".intval($postId)." AND user_id=".intval($userId)." AND gg_id=".intval($goodId));
		return self::_getList($result,'TradeOfferData','id');
	}
	
	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}
}
?>
