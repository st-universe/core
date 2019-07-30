<?php
class UserProfileVisitorsData extends BaseTable {

	protected $tablename = 'stu_user_profile_visitors';
	const tablename = 'stu_user_profile_visitors';

	function __construct(&$data = array()) {
		$this->data = $data;
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

	function getRecipientId() {
		return $this->data['recipient'];
	}

	private $user = NULL;

	function getUser() {
		if ($this->user === NULL) {
			$this->user = new User($this->getUserId());
		}
		return $this->user;
	}

	private $recipient = NULL;

	function getRecipient() {
		if ($this->recipient === NULL) {
			$this->recipient = new User($this->getRecipientId());
		}
		return $this->recipient;
	}

	function setRecipientId($value) {
		$this->data['recipient'] = $value;
		$this->addUpdateField('recipient','getRecipientId');
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	function setDate($value) {
		$this->data['date'] = $value;
		$this->addUpdateField('date','getDate');
	}

	function getDate() {
		return $this->data['date'];
	}

}
class UserProfileVisitors extends UserProfileVisitorsData {

	static function getRecentList($userId) {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".parent::tablename." WHERE recipient=".intval($userId)." AND date>=".(time()-86400)." ORDER BY date DESC");
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[] = new UserProfileVisitorsData($data);
		}
		return $ret;
	}

	static function hasVisit($userId,$visitor) {
		return DB()->query("SELECT COUNT(*) FROM ".parent::tablename." WHERE recipient=".intval($userId)." AND user_id=".intval($visitor),1);
	}

	static function registerVisit($userId,$visitor) {
		$obj = new UserProfileVisitorsData;
		$obj->setRecipientId($userId);
		$obj->setUserId($visitor);
		$obj->setDate(time());
		$obj->save();
	}

	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}

}

?>
