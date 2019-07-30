<?php
class IgnorelistData extends BaseTable {
	
	protected $tablename = 'stu_ignorelist';
	const tablename = 'stu_ignorelist';
	
	function __construct($data=array()) {
		$this->data = &$data;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
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

	function isOwnIgnore() {
		return currentUser()->getId() == $this->getUserId();
	}
}

class Ignorelist extends IgnorelistData {

	static function isOnList($userId,$value) {
		return DB()->query("SELECT COUNT(id) FROM stu_ignorelist WHERE user_id=".$userId." AND recipient=".$value,1);
	}

	static function getList($userId) {
		$result = DB()->query("SELECT * FROM stu_ignorelist WHERE user_id=".$userId." ORDER BY recipient");
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new IgnorelistData($data);
		}
		return $ret;
	}

	static function getRemoteIgnores($userId) {
		$result = DB()->query("SELECT * FROM stu_ignorelist WHERE recipient=".$userId." ORDER BY user_id");
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new ContactlistData($data);
		}
		return $ret;
	}

	static function truncate($sql='') {
		DB()->query("DELETE FROM ".self::tablename." ".$sql);
	}

	static function getById($contactId) {
		$result = DB()->query("SELECT * FROM stu_ignorelist WHERE id=".intval($contactId)." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new IgnorelistData($result);
	}

}
?>
