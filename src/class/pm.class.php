<?php

class PMData extends Basetable {

	const tablename = 'stu_pms';
	protected $tablename = 'stu_pms';
	
	private $sender = NULL;
	private $recipient = NULL;

	function __construct($data=NULL) {
		$this->data = &$data;
	}

	function isNew() {
		return $this->getNew() == 1;	
	}

	function getNew() {
		return $this->data['new'];
	}

	function setIsNew($value) {
		$this->data['new'] = $value;
		$this->addUpdateField('new','getNew');
	}

	function isMarkableAsNew() {
		if (!$this->isNew()) {
			return FALSE;
		}
		$this->setIsNew(0);
		$this->save();
		return TRUE;
	}

	function getId() {
		return $this->data['id'];
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getSender() {
		if ($this->sender === NULL) {
			$this->sender = new User($this->getSenderId());
		}
		return $this->sender;
	}

	function getSenderId() {
		return $this->data['send_user'];
	}

	function setSenderId($value) {
		$this->data['send_user'] = $value;
		$this->addUpdateField('send_user','getSenderId');
	}

	function getRecipientId() {
		return $this->data['recip_user'];
	}

	function setRecipientId($value) {
		$this->data['recip_user'] = $value;
		$this->addUpdateField('recip_user','getRecipientId');
	}

	function getRecipient() {
		if ($this->recipient === NULL) {
			$this->recipient = new User($this->getRecipientId());
		}
		return $this->recipient;
	}

	function getText() {
		return $this->data['text'];
	}

	function setText($value) {
		$this->data['text'] = encodeString(decodeString($value));
		$this->addUpdateField('text','getText');
	}

	function getDateFormatted() {
		return date("d.m.Y H:i",$this->getDate());
	}

	function getDate() {
		return $this->data['date'];
	}

	function setDate($value) {
		$this->data['date'] = $value;
		$this->addUpdateField('date','getDate');
	}

	function getTextCutted() {
		if (strlen($this->getText()) < 400) {
			return $this->getText();
		}
		return substr($this->getText(),0,397)."...";
	}

	function getTextParsed() {
		return nl2br(BBCode()->parse($this->getTextDecoded()));
	}

	function getTextDecoded() {
		return stripslashes(decodeString($this->getText()));
	}

	public function getTextDecodedRaw() {
		return stripslashes(decodeString($this->getText(),FALSE));
	}

	function getCategoryId() {
		return $this->data['cat_id'];
	}

	function setCategoryId($value) {
		$this->data['cat_id'] = $value;
		$this->addUpdateField('cat_id','getCategoryId');
	}

	function getReplied() {
		return $this->data['replied'];
	}

	function setReplied($value) {
		$this->data['replied'] = $value;
		$this->addUpdateField('replied','getReplied');
	}

	function isReplied() {
		return $this->getReplied() == 1;
	}

	function getCacheValue() {
		return $this->getId()."_".$this->getCategoryId()."_".$this->getReplied();
	}

	function copyPM() {
		$newobj = clone($this);
		$newobj->setSenderId($this->getRecipientId());
		$newobj->setRecipientId($this->getSenderId());
		$cat = PMCategory::getOrGenSpecialCategory(PM_SPECIAL_PMOUT,$this->getSenderId());
		$newobj->setCategoryId($cat->getId());
		$newobj->setNew(0);
		$newobj->save();
	}

	function isOwnPM() {
		return $this->getRecipientId() == currentUser()->getId();
	}

	private $senderignore = NULL;

	function senderIsIgnored() {
		if ($this->senderignore === NULL) {
			$this->senderignore = Ignorelist::isOnList(currentUser()->getId(),$this->getSenderId());
		}
		return $this->senderignore;
	}

	private $sendercontact = NULL;

	function senderIsContact() {
		if ($this->sendercontact === NULL) {
			$this->sendercontact = Contactlist::isOnList(currentUser()->getId(),$this->getSenderId());
		}
		return $this->sendercontact;
	}

	/**
	 */
	public function displayUserLinks() { #{{{
		return $this->getSender() && $this->getSender()->getId() != USER_NOONE;
	} # }}}

}
class PM extends PMData {
	
	function __construct($id=0) {
		$data = DB()->query("SELECT * FROM ".$this->getTable()." WHERE id=".intval($id)." LIMIT 1",4);
		if ($data == 0) {
			new ObjectNotFoundException($id);
		}
		parent::__construct($data);
	}

	static function getPMsBy($catId,$mark) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE cat_id=".$catId." AND recip_user=".currentUser()->getId()." ORDER BY id DESC LIMIT ".$mark.",".PMLIMITER);
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new PMData($data);
		}
		return $ret;
	}

	static function getPMById($pmid) {
		$data = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".intval($pmid)." LIMIT 1",4);
		if ($data == 0) {
			return FALSE;
		}
		return new PMData($data);
	}

	static function sendPM($sender,$recipient,$text,$category=PM_SPECIAL_MAIN) {
		if ($sender == $recipient) {
			return;
		}
		$pm = new PMData();
		$pm->setDate(time());
		$cat = PMCategory::getOrGenSpecialCategory($category,$recipient);
		$pm->setCategoryId($cat->getId());
		$pm->setText($text);
		$pm->setRecipientId($recipient);
		$pm->setSenderId($sender);
		if ($sender != USER_NOONE) {
			$pm->copyPM();
		}
		$pm->save();
	}

	/**
	 */
	static function getObjectsBy($sql='') { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'PMData');
	} # }}}

}
?>
