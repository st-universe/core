<?php

class ResearchUserData extends BaseTable {

	protected $tablename = 'stu_researched';
	const tablename = 'stu_researched';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getResearchId() {
		return $this->data['research_id'];
	}

	public function setResearchId($value) {
		$this->setFieldValue('research_id',$value,'getResearchId');
	}

	public function getUserId() {
		return $this->data['user_id'];
	}

	public function setUserId($value) {
		$this->setFieldValue('user_id',$value,'getUserId');
	}

	public function getActive() {
		return $this->data['aktiv'];
	}

	public function setActive($value) {
		$this->setFieldValue('aktiv',$value,'getActive');
	}

	public function getResearch() {
		return ResourceCache()->getObject('research',$this->getResearchId());
	}

	public function getFinishedDate() {
		return $this->data['finished'];
	}

	public function setFinishedDate($value) {
		$this->setFieldValue('finished',$value,'getFinishedDate');
	}

	public function getFinishedDateFormatted() {
		return date("d.m.Y",$this->getFinishedDate());
	}

	public function isResearchInProgress() {
		return $this->getActive() > 0;
	}

	public function isResearchFinished() {
		return $this->getFinishedDate() > 0;
	}

	/**
	 */
	public function getUser() { #{{{
		return ResourceCache()->getObject('user',$this->getUserId());
	} # }}}

	/**
	 */
	public function finish() { #{{{
		$this->setActive(0);
		$this->setFinishedDate(time());
		PM::sendPM(USER_NOONE,$this->getUser()->getId(),"Forschung '".$this->getResearch()->getName()."' wurde abgeschlossen",PM_SPECIAL_COLONY);
		$this->createDatabaseEntries();
		$this->createShipRumpEntries();
	} # }}}

	/**
	 */
	private function createShipRumpEntries() { #{{{
		if (!$this->getResearch()->getRumpId()) {
			return;
		}
		if (RumpUser::countInstances('user_id='.$this->getUserId().' AND rump_id='.$this->getResearch()->getRumpId()) > 0) {
			return FALSE;
		}
		$entry = new RumpUserData;
		$entry->setUserId($this->getUserId());
		$entry->setRumpId($this->getResearch()->getRumpId());
		$entry->save();
	} # }}}

	/**
	 */
	private function createDatabaseEntries() { #{{{
		if (!$this->getResearch()->getDatabaseEntries()) {
			return;
		}
		$entries = explode(',',$this->getResearch()->getDatabaseEntries());
		foreach ($entries as $entry) {
			databaseScan($entry,$this->getUserId());
		}
	} # }}}

	/**
	 */
	public function advance($amount) { #{{{
		if ($this->getActive()-$amount <= 0) {
			$this->finish();
		} else {
			$this->setActive($this->getActive()-$amount);
		}
		$this->save();
	} # }}}

}
class ResearchUser extends ResearchUserData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($crewId);
		}
		return parent::__construct($result);
	}

	static function getCurrentResearch($userId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE aktiv>0 ANd user_id=".intval($userId),4);
		if ($result == 0) {
			return FALSE;
		}
		return new ResearchUserData($result);
	}

	static function getByResearch($researchId,$userId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE research_id=".intval($researchId)." AND user_id=".intval($userId),4);
		if ($result == 0) {
			return FALSE;
		}
		return self::_getBy($result,$researchId,'ResearchUserData');
	}

	static function getFinishedListByUser($userId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE user_id=".intval($userId)." AND finished>0 ORDER BY finished ASC");
		return self::_getList($result,'ResearchUserData','research_id');
	}

	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}

}
?>
