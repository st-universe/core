<?php

class TradePostData extends BaseTable {

	protected $tablename = 'stu_trade_posts';
	const tablename = 'stu_trade_posts';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getUserId() {
		return $this->data['user_id'];
	}

	public function setUserId($value) {
		$this->setFieldValue('user_id',$value,'getUserId');
	}

	public function getName() {
		return $this->data['name'];
	}

	public function setName($value) {
		$this->setFieldValue('name',$value,'getName');
	}

	public function getShipId() {
		return $this->data['ship_id'];
	}

	public function getShip() {
		return ResourceCache()->getObject('ship',$this->getShipId());
	}

	public function setShipId($value) {
		$this->setFieldValue('ship_id',$value,'getShipId');
	}

	public function getTradeNetwork() {
		return $this->data['trade_network'];
	}

	public function setTradeNetwork($value) {
		$this->setFieldValue('trade_network',$value,'getTradeNetwork');
	}

	public function getLevel() {
		return $this->data['level'];
	}

	public function setLevel($value) {
		$this->setFieldValue('level',$value,'getLevel');
	}

	public function getFreightherCount() {
		return $this->data['freighter'];
	}

	public function setFreighterCount($value) {
		$this->setFieldValue('freighter',$value,'getFreightherCount');
	}

	public function getTransferCapacity() {
		return $this->data['transfer_capacity'];
	}

	public function setTransferCapacity($value) {
		$this->setFieldValue('transfer_capacity',$value,'getTransferCapacity');
	}

	public function getStorage() {
		return $this->data['storage'];
	}

	public function setStorage($value) {
		$this->setFieldValue('storage',$value,'getStorage');
	}

	public function getLicenceCount() {
		return TradeLicences::getLicenceCountByTradepost($this->getId());	
	}

	public $userHasLicence = NULL;

	public function currentUserHasLicence() {
		if ($this->userHasLicence === NULL) {
			$this->userHasLicence = TradeLicences::userHasLicence($this->getId(),currentUser()->getId());
		}
		return $this->userHasLicence;
	}

	public function getDescription() {
		return $this->data['description'];
	}

	public function setDescription($value) {
		$this->setFieldValue('description',$value,'getDescription');
	}

	public function getDescriptionFormatted() {
		return nl2br($this->getDescription());
	}

	public function calculateLicenceCost() {
		// @todo Kostenkalkukation
		return 1;
	}

	public function getLicenceCostGood() {
		// @todo Kann auch was anderes als Dilithium sein
		return ResourceCache()->getObject('good',GOOD_DILITHIUM);
	}

	/**
	 */
	public function getStorageByCurrentUser() { #{{{
		return $this->getStorageByUser(currentUser()->getId());
	} # }}}

	private $storageByUser = array();

	public function getStorageByUser($user_id) {
		if (!array_key_exists($user_id,$this->storageByUser)) {
			$this->storageByUser[$user_id] = TradeStorage::getStorageByTradepostUser($this->getId(),$user_id);
		}
		return $this->storageByUser[$user_id];
	}

	public function upperStorage($user_id,$goodId,$count) {
		$data = &$this->getStorageByUser($user_id)->getStorage();
		if (!array_key_exists($goodId,$data)) {
			$stor = new TradeStorageData;
			$stor->setUserId($user_id);
			$stor->setGoodId($goodId);
			$stor->setTradePostId($this->getId());
			$this->getStorageByUser($user_id)->addStorageEntry($stor);
		} else {
			$stor = &$data[$goodId];
		}
		$stor->upperCount($count);
		$stor->save();
	}

	public function lowerStorage($user_id,$goodId,$count) {
		$data = &$this->getStorageByUser($user_id)->getStorage();
		if (!array_key_exists($goodId,$data)) {
			return;
		}
		$stor = &$data[$goodId];
		if ($stor->getAmount() <= $count) {
			$stor->deleteFromDatabase();
			return;
		}
		$stor->lowerCount($count);
		$stor->save();
	}

	private $offerStorageByUser = NULL;

	public function getOfferStorageByCurrentUser() {
		if ($this->offerStorageByUser === NULL) {
			$this->offerStorageByUser = TradeOffer::getStorageByTradepostUser($this->getId(),currentUser()->getId());
		}
		return $this->offerStorageByUser;
	}

	private $storageSum = NULL;
	private $freeCapacity = NULL;

	public function getStorageSum() {
		if ($this->storageSum === NULL) {
			$this->storageSum = $this->getStorageByUser(currentUser()->getId())->getStorageSum();
		}
		return $this->storageSum;
	}

	public function getTransferCapacitySum() {
		if ($this->freeCapacity === NULL) {
			$this->freeCapacity = TradeTransfer::getSumByTradepost($this->getId(),currentUser()->getId());
		}
		return $this->freeCapacity;
	}

	public function getFreeStorage() {
		if ($this->getStorage()-$this->getStorageSum() < 0) {
			return 0;
		}
		return $this->getStorage()-$this->getStorageSum();
	}

	public function getFreeTransferCapacity() {
		return $this->getTransferCapacity()-$this->getTransferCapacitySum();
	}

	public function currentUserIsOverStorage() {
		return $this->getStorageByUser(currentUser()->getId())->getStorageSum() > $this->getStorage();
	}

	public function currentUserCanBuyLicence() {
		return TradeLicences::countInstances('user_id='.currentUser()->getId()) < MAX_TRADELICENCE_COUNT;
	}
}
class TradePost extends TradePostData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getListByLicences($userId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id IN (SELECT posts_id FROM stu_trade_licences WHERE user_id=".intval($userId).")");
		return self::_getList($result,'TradePostData');
	}
}
?>
