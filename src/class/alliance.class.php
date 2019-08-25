<?php

class AllianceData extends BaseTable {

	protected $tablename = 'stu_alliances';
	const tablename = 'stu_alliances';
	
	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getName() {
		return $this->data['name'];
	}

	function setName($value) {
		$this->data['name'] = strip_tags($value);
		$this->addUpdateField('name','getName');
	}

	function getNameWithoutMarkup() {
		return strip_tags(BBCode()->parse($this->getName()));
	}

	function getHomepage() {
		return $this->data['homepage'];
	}
	/**
	 */
	public function getHomepageDisplay() { #{{{
		return stripslashes($this->getHomepage());
	} # }}}


	function setHomepage($value) {
		$this->data['homepage'] = strip_tags($value);
		$this->addUpdateField('homepage','getHomepage');
	}

	function getDescription() {
		return $this->data['description'];
	}

	function setDescription($value) {
		$this->data['description'] = strip_tags($value);
		$this->addUpdateField('description','getDescription');
	}

	function setCSSClass($value) {
		$this->data['cssclass'] = $value;
	}

	function getCSSClass() {
		switch ($this->data['cssclass']) {
			case 0:
				return 'odd';
			case 1:
				return 'even';
		}
	}

	function getFactionId() {
		return $this->data['faction_id'];
	}

	function setFactionId($value) {
		$this->data['faction_id'] = $value;
		$this->addUpdateField('faction_id','getFactionId');
	}

	function getDate() {
		return $this->data['date'];
	}

	function setDate($value) {
		$this->data['date'] = $value;
		$this->addUpdateField('date','getDate');
	}

	function isInAlliance() {
		return array_key_exists(currentUser()->getId(),$this->getMembers());
	}

	private $founder = NULL;

	function getFounder() {
		if ($this->founder === NULL) {
			$this->founder = AllianceJobs::getByType($this->getId(),ALLIANCE_JOBS_FOUNDER);
		}
		return $this->founder;
	}

	function setFounder($userId) {
		$obj = AllianceJobs::getByType($this->getId(),ALLIANCE_JOBS_FOUNDER);
		if (!$obj) {
			$obj = new AllianceJobsData;
			$obj->setType(ALLIANCE_JOBS_FOUNDER);
			$obj->setAllianceId($this->getId());
		}
		$obj->setUserId($userId);
		$obj->save();
		$this->founder = $obj;
	}

	private $successor = NULL;

	function getSuccessor() {
		if ($this->successor === NULL) {
			$this->successor = AllianceJobs::getByType($this->getId(),ALLIANCE_JOBS_SUCCESSOR);
		}
		return $this->successor;
	}

	function setSuccessor($userId) {
		$obj = AllianceJobs::getByType($this->getId(),ALLIANCE_JOBS_SUCCESSOR);
		if (!$obj) {
			$obj = new AllianceJobsData;
			$obj->setType(ALLIANCE_JOBS_SUCCESSOR);
			$obj->setAllianceId($this->getId());
		}
		$obj->setUserId($userId);
		$obj->save();
		$this->successor = $obj;
	}

	function delSuccessor() {
		$obj = AllianceJobs::getByType($this->getId(),ALLIANCE_JOBS_SUCCESSOR);
		if ($obj) {
			$obj->deleteFromDatabase();
		}
	}

	private $diplomatic = NULL;

	function getDiplomatic() {
		if ($this->diplomatic === NULL) {
			$this->diplomatic = AllianceJobs::getByType($this->getId(),ALLIANCE_JOBS_DIPLOMATIC);
		}
		return $this->diplomatic;
	}

	function setDiplomatic($userId) {
		$obj = AllianceJobs::getByType($this->getId(),ALLIANCE_JOBS_DIPLOMATIC);
		if (!$obj) {
			$obj = new AllianceJobsData;
			$obj->setType(ALLIANCE_JOBS_DIPLOMATIC);
			$obj->setAllianceId($this->getId());
		}
		$obj->setUserId($userId);
		$obj->save();
		$this->diplomatic = $obj;
	}

	function delDiplomatic() {
		$obj = AllianceJobs::getByType($this->getId(),ALLIANCE_JOBS_DIPLOMATIC);
		if ($obj) {
			$obj->deleteFromDatabase();
		}
	}

	private $members = NULL;

	function getMembers() {
		if ($this->members === NULL) {
			foreach (User::getListBy('WHERE allys_id='.$this->getId()) as $user) {
				$this->members[$user->getId()] = new \Lib\AllianceMemberWrapper($user,$this);
			}
		}
		return $this->members;
	}

	function getMemberCount() {
		return count($this->getMembers());
	}

	function currentUserMayEdit() {
		return ($this->getSuccessor() && currentUser()->getId() == $this->getSuccessor()->getUserId()) || currentUser()->getId() == $this->getFounder()->getUserId();
	}

	function mayEditFactionMode() {
		if ($this->isNew()) {
			return TRUE;
		}
		if ($this->getMemberCount() == 1) {
			return TRUE;
		}
		if ($this->getFactionId() != 0) {
			return TRUE;
		}
		foreach ($this->getMembers() as $key => $obj) {
			if ($obj->getUser()->getFaction() != currentUser()->getFaction()) {
				return FALSE;
			}
		}
		return TRUE;
	}

	function setAcceptApplications($value) {
		$this->data['accept_applications'] = $value;
		$this->addUpdateField('accept_applications','getAcceptApplications');
	}

	function getAcceptApplications() {
		return $this->data['accept_applications'];
	}

	function currentUserMaySignup() {
		if (AllianceJobs::hasPendingApplication(currentUser()->getId())) {
			return FALSE;
		}
		return $this->getAcceptApplications() && !currentUser()->isInAlliance() && ($this->getFactionId() == 0 || currentUser()->getFaction() == $this->getFactionId());
	}

	private $pendingApplications = NULL;

	function getPendingApplications() {
		if ($this->pendingApplications === NULL) {
			$this->pendingApplications = AllianceJobs::getList("type=".ALLIANCE_JOBS_PENDING." AND alliance_id=".$this->getId());
		}
		return $this->pendingApplications;
	}

	function currentUserIsFounder() {
		return $this->getFounder()->getUserId() == currentUser()->getId();
	}

	function currentUserCanLeave() {
		return $this->getId() == currentUser()->getAllianceId() && !$this->currentUserIsFounder();
	}

	function truncateJobCache() {
		$this->founder = NULL;
		$this->successor = NULL;
		$this->diplomatic = NULL;
	}

	function getAvatar() {
		return $this->data['avatar'];
	}

	function setAvatar($value) {
		$this->data['avatar'] = $value;
		$this->addUpdateField('avatar','getAvatar');
	}

	function getFullAvatarPath() {
		return AVATAR_ALLIANCE_PATH."/".$this->getAvatar().".png";
	}

	function currentUserIsDiplomatic() {
		if (!$this->getDiplomatic()) {
			return $this->currentUserMayEdit();
		}
		return $this->currentUserMayEdit() || $this->getDiplomatic()->getUserId() == currentUser()->getId(); 
	}

	function sendMessage($text) {
                PM::sendPM(USER_NOONE,$this->getFounder()->getUserId(),$text);
                if ($this->getSuccessor()) {
                        PM::sendPM(USER_NOONE,$this->getSuccessor()->getUserId(),$text);
                }
                if ($this->getDiplomatic()) {
                        PM::sendPM(USER_NOONE,$this->getDiplomatic()->getUserId(),$text);
                }
	}

	public function hasFriendlyRelation($allianceId) {
		return AllianceRelation::getBy('alliance_id IN ('.$this->getId().','.$allianceId.') AND recipient IN ('.$allianceId.','.$this->getId().') AND type IN ('.ALLIANCE_RELATION_FRIENDS.','.ALLIANCE_RELATION_ALLIED.')');	
	}

	public function hasHostileRelation($allianceId) {
		return AllianceRelation::getBy('alliance_id IN ('.$this->getId().','.$allianceId.') AND recipient IN ('.$allianceId.','.$this->getId().') AND type='.ALLIANCE_RELATION_WAR);	
	}

	/**
	 */
	public function delete() { #{{{
		$list = AllianceJobs::getList('alliance_id='.$this->getId());
		foreach($list as $key => $obj) {
			$obj->deleteFromDatabase();
		}
		$list = AllianceRelation::getList('alliance_id='.$this->getId().' OR recipient='.$this->getId());
		foreach($list as $key => $obj) {
			$obj->deleteFromDatabase();
		}
		$list = AllianceBoard::getList('alliance_id='.$this->getId());
		foreach ($list as $key => $obj) {
			$obj->deepDelete();
		}
		$text = "Die Allianz ".$this->getNameWithoutMarkup()." wurde aufgelöst";
		$list = $this->getMembers();
		foreach ($list as $key => $obj) {
			if ($this->getFounder()->getUserId() != $obj->getUserId()) {
				PM::sendPM(USER_NOONE,$obj->getUserId(),$text);
			}
			$obj->getUser()->setAllianceId(0);
			$obj->getUser()->save();
		}
		if ($this->getAvatar()) {
			@unlink(APP_PATH.'/src/'.AVATAR_ALLIANCE_PATH.$this->getAvatar().".png");
		}
		$this->deleteFromDatabase();
	} # }}}

	/**
	 */
	public function handleFounderDeletion() { #{{{
		if (!$this->getSuccessor()) {
			$this->delete();
			return;
		}
		$this->handleFounderChance();
	} # }}}

	/**
	 */
	public function handleFounderChange() { #{{{
		$this->setFounder($this->getSuccessor()->getUserId());		
		$this->delSuccessor();
		$this->save();
	} # }}}

	/**
	 */
	public function currentUserIsBoardModerator() { #{{{
		// XXX: for now, president and successor are enough
		return $this->currentUserMayEdit();
	} # }}}

}
class Alliance extends AllianceData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getList() {
		$ret = array();
		$i = 0;
		$result = DB()->query("SELECT * FROM ".self::tablename." ORDER BY id");
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[$data['id']] = new AllianceData($data);
			$ret[$data['id']]->setCSSClass($i%2);
			$i++;
		}
		return $ret;
	}

	static function getById($allianceId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".intval($allianceId)." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new AllianceData($result);
	}
}
?>
