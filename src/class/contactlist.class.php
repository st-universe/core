<?php

class ContactlistData extends BaseTable {

	protected $tablename = 'stu_contactlist';
	const tablename = 'stu_contactlist';

	const CONTACT_FRIEND = 1;
	const CONTACT_ENEMY = 2;
	const CONTACT_NEUTRAL = 3;
	
	function __construct($data=array()) {
		$this->data = &$data;
	}

	function getId() {
		return $this->data['id'];
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	function getDate() {
		return $this->data['date'];
	}

	function setDate($value) {
		$this->data['date'] = $value;
		$this->addUpdateField('date','getDate');
	}

	function getRecipientId() {
		return $this->data['recipient'];
	}

	function setRecipientId($value) {
		$this->data['recipient'] = $value;
		$this->addUpdateField('recipient','getRecipientId');
	}

	function getMode() {
		return $this->data['mode'];
	}

	function getModeDescription() {
		switch ($this->getMode()) {
			case self::CONTACT_FRIEND:
				return "Freund";
			case self::CONTACT_ENEMY:
				return "Feind";
			case self::CONTACT_NEUTRAL:
				return "Neutral";
		}
	}

	/**
	 */
	public function isFriendly() { #{{{
		return $this->getMode() == CONTACT_FRIEND;
	} # }}}

	/**
	 */
	public function isEnemy() { #{{{
		return $this->getMode() == CONTACT_ENEMY;
	} # }}}

	function setMode($value) {
		$this->data['mode'] = $value;
		$this->addUpdateField('mode','getMode');
	}

	function getComment() {
		return $this->data['comment'];
	}

	function setComment($value) {
		$this->setFieldValue('comment',$value,'getComment');
	}

	/**
	 */
	public function getCommentStyle() { #{{{
		if ($this->getComment()) {
			return '';
		}
		return 'display: none;';
	} # }}}

	/**
	 */
	public function getCommentParsed() { #{{{
		return stripslashes($this->getComment());
	} # }}}


	private $recipient = NULL;

	function getRecipient() {
		if ($this->recipient === NULL) {
			$this->recipient = new User($this->getRecipientId());
		}
		return $this->recipient;
	}

	private $user = NULL;

	function getUser() {
		if ($this->user === NULL) {
			$this->user = new User($this->getUserId());
		}
		return $this->user;
	}
}
class Contactlist extends ContactlistData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".$this->getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);

	}

	static function getList($userId) {
		return self::getObjectsBy(" WHERE user_id=".$userId." ORDER BY recipient");
	}

	/**
	 */
	static function getObjectsBy($where='') { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$where);
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new ContactlistData($data);
		}
		return $ret;
	} # }}}


	static function isOnList($userId,$value) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE user_id=".$userId." AND recipient=".$value." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new ContactlistData($result);
	}

	static function getById($contactId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".intval($contactId)." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new ContactlistData($result);
	}

	static function truncate($sql='') {
		DB()->query("DELETE FROM ".self::tablename." ".$sql);
	}

	static function getRemoteContacts($userId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE recipient=".$userId." AND mode IN (".self::CONTACT_FRIEND.",".self::CONTACT_ENEMY.") ORDER BY mode,user_id");
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new ContactlistData($data);
		}
		return $ret;
	}

	static public function isFriendlyContact($userId,$recipient) {
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." WHERE user_id=".intval($userId)." AND recipient=".intval($recipient)." AND mode=".self::CONTACT_FRIEND,1);
	}



	/**
	 */
	static function hasContact($userId,$recipient) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE user_id=".intval($userId)." AND recipient=".intval($recipient),4);
		if ($result == 0) {
			return FALSE;
		}
		return new ContactlistData($result);
	} # }}}

}
?>
