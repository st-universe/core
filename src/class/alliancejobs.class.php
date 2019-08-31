<?php

class AllianceJobsData extends BaseTable {

	protected $tablename = 'stu_alliances_jobs';
	const tablename = 'stu_alliances_jobs';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getAllianceId() {
		return $this->data['alliance_id'];
	}

	function setAllianceId($value) {
		$this->data['alliance_id'] = $value;
		$this->addUpdateField('alliance_id','getAllianceId');
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	private $alliance = NULL;

	function getAlliance() {
		if ($this->alliance === NULL) {
			$this->alliance = new Alliance($this->getAllianceId());
		}
		return $this->alliance;
	}

	private $user = NULL;

	function getUser() {
		if ($this->user === NULL) {
			$this->user = new User($this->getUserId());
		}
		return $this->user;
	}

	function setType($value) {
		$this->data['type'] = $value;
		$this->addUpdateField('type','getType');
	}

	function getType() {
		return $this->data['type'];
	}

	function denyApplication() {
		$text = "Deine Bewerbung bei der Allianz ".$this->getAlliance()->getNameWithoutMarkup()." wurde abgelehnt";
		PM::sendPM(USER_NOONE,$this->getUserId(),$text);
		$this->deleteFromDatabase();
	}

}
class AllianceJobs extends AllianceJobsData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".$this->getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getByType($allianceId,$type) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE alliance_id=".intval($allianceId)." AND type=".$type." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new AllianceJobsData($result);
	}

	static function truncatePendingMembers($allianceId) {
		$result = self::getByType($allianceId,ALLIANCE_JOBS_PENDING);
		foreach($result as $key => $value) {
			$value->denyApplication();
		}
	}

	static function countInstances($sql) {
		return DB()->query("SELECT COUNT(id) FROM ".parent::tablename." ".$sql,1);
	}

	static function hasPendingApplication($userId) {
		return self::countInstances("WHERE user_id=".$userId." AND type=".ALLIANCE_JOBS_PENDING);
	}

	static function getList($sql) {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".parent::tablename." WHERE ".$sql);
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new AllianceJobsData($data);
		}
		return $ret;
	}

	static function delByUser($uid) {
		DB()->query("DELETE FROM ".parent::tablename." WHERE user_id=".intval($uid));
	}

	static function getPossibleTypes() {
		$arr = array(ALLIANCE_JOBS_FOUNDER => 1,ALLIANCE_JOBS_SUCCESSOR => 1,ALLIANCE_JOBS_DIPLOMATIC => 1);
		return $arr;
	}

	/**
	 */
	static function getByFounder($userId) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE type=".ALLIANCE_JOBS_FOUNDER." AND user_id=".intval($userId),4);
		if ($result == 0) {
			return FALSE;
		}
		return new AllianceJobsData($result);
	} # }}}

}
?>
