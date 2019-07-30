<?php

class TradeLicencesData extends BaseTable {

	protected $tablename = 'stu_trade_licences';
	const tablename = 'stu_trade_licences';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getTradePostId() {
		return $this->data['posts_id'];
	}

	public function setTradePostId($value) {
		$this->setFieldValue('posts_id',$value,'getTradePostId');
	}

	public function getUserId() {
		return $this->data['user_id'];
	}

	public function setUserId($value) {
		$this->setFieldValue('user_id',$value,'getUserId');
	}

	public function getDate() {
		return $this->data['date'];
	}

	public function setDate($value) {
		$this->setFieldValue('date',$value,'getDate');
	}

	public function getUser() {
		return ResourceCache()->getObject('user',$this->getUserId());
	}

	public function getDateFormatted() {
		return date("d.m.Y H:i",$this->getDate());
	}
}
class TradeLicences extends TradeLicencesData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getPostsByUser($userId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE user_id=".intval($userId));	
		return self::_getList($result,'TradeLicencesData');
	}

	static function getLicenceCountByTradepost($postId) {
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." WHERE posts_id=".intval($postId),1);
	}

	static function userHasLicence($postId,$userId) {
		return DB()->query("SELECT id FROM ".self::tablename." WHERE posts_id=".intval($postId)." AND user_id=".intval($userId),1) > 0;
	}

	static function countInstances($qry) {
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." WHERE ".$qry,1);
	}

	static function getLicencesByTradePost($postId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE posts_id=".intval($postId));
		return self::_getList($result,'TradeLicencesData');
	}

	/**
	 */
	static function hasLicenceInNetwork($userId,$tradeNetworkId) { #{{{
		return self::countInstances('user_id='.intval($userId).' AND posts_id IN (SELECT id FROM stu_trade_posts WHERE trade_network='.intval($tradeNetworkId).')');
	} # }}}
	
	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}

}
?>
