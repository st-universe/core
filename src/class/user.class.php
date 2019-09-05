<?php

use Stu\Lib\ContactlistWrapper;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;

class UserData extends BaseTable {

	protected $tablename = 'stu_user';
	const tablename = 'stu_user';

	function __construct($data) {
		$this->data = $data;
	}	

	function getId() {
		return (int) $this->data['id'];
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function isInAlliance() {
		return $this->data['allys_id'] > 0;
	}

	function setAllianceId($value) {
		$this->data['allys_id'] = $value;
		$this->addUpdateField('allys_id','getAllianceId');
	}

	function getAllianceId() {
		return $this->data['allys_id'];
	}

	function getKNMark() {
		return $this->data['kn_lez'];
	}

	function setKNMark($value) {
		$this->data['kn_lez'] = $value;
		$this->addUpdateField('kn_lez','getKNMark');
	}

	function getFaction() {
		return $this->data['race'];
	}

	function setFaction($value) {
		$this->data['race'] = $value;
		$this->addUpdateField('race','getFaction');
	}

	function getName() {
		return $this->getUser();
	}

	function getUser() {
		return $this->data['user'];
	}

	function setUser($value) {
		$this->data['user'] = $value;
		$this->setFieldValue('user',$value,'getUser');
	}

	function getPassword() {
		return $this->data['pass'];
	}

	function setPassword($value) {
		$this->setFieldValue('pass',$value,'getPassword');
	}

	function getAvatar() {
		return $this->data['propic'];
	}

	function setAvatar($value) {
		$this->data['propic'] = $value;
		$this->addUpdateField('propic','getAvatar');
	}

	function getEmail() {
		return $this->data['email'];
	}

	function setEmail($value) {
		$this->data['email'] = $value;
		$this->addUpdateField('email','getEmail');
	}

	function getDescription() {
		return $this->data['description'];
	}

	function setDescription($value) {
		$this->data['description'] = $value;
		$this->addUpdateField('description','getDescription');
	}

	public function getEmailNotification() {
		return $this->data['email_notification'];
	}

	public function setEmailNotification($value) {
		$this->setFieldValue('email_notification',$value,'getEmailNotification');
	}

	public function getStorageNotification() {
		return $this->data['storage_notification'];
	}

	public function setStorageNotification($value) {
		$this->setFieldValue('storage_notification',$value,'getStorageNotification');
	}

	function getFullAvatarPath() {
		if (!$this->getAvatar()) {
			return "/assets/rassen/".$this->getFaction()."kn.png";
		}
		return AVATAR_USER_PATH."/".$this->getAvatar().".png";
	}

	function isOnline() {
		if ($this->getLastAction() < time()-USER_ONLINE_PERIOD) {
			return FALSE;
		}
		return TRUE;
	}

	function getLastAction() {
		return $this->data['lastaction'];
	}

	function getActive() {
		return $this->data['aktiv'];
	}

	function setActive($value) {
		$this->data['aktiv'] = $value;
		$this->addUpdateField('aktiv','getActive');
	}

	function setCreationDate($value) {
		$this->data['creation'] = $value;
		$this->addUpdateField('creation','getCreationDate');
	}

	function getCreationDate() {
		return $this->data['creation'];
	}

	function getDeletionMark() {
		return $this->data['delmark'];
	}

	public function setVacationMode($value) {
		$this->setFieldValue('vac_active',$value,'getVacationMode');
	}

	public function getVacationMode() {
		return $this->data['vac_active'];
	}

	private $friends = NULL;

	function getFriends() {
		if ($this->friends === NULL) {
			$this->friends = User::getListBy("WHERE id IN (SELECT user_id FROM stu_contactlist WHERE mode=1 AND recipient=".$this->getId().")
				      OR id IN (SELECT id FROM stu_user WHERE allys_id>0 AND allys_id=".$this->getAllianceId().") AND id!=".$this->getId()." GROUP BY id ORDER BY id"); 
		}
		return $this->friends;
	}

	private $alliance = NULL;

	function getAlliance() {
		if ($this->alliance === NULL) {
			$this->alliance = new Alliance($this->getAllianceId());
		}
		return $this->alliance;
	}

	function getTick() {
		return $this->data['tick'];
	}

	function setTick($value) {
		$this->data['tick'] = $value;
		$this->addUpdateField('tick','getTick');
	}

	function setLogin($value) {
		$this->data['login'] = $value;
		$this->addUpdateField('login','getLogin');
	}

	function getLogin() {
		return $this->data['login'];
	}

	public function setSaveLogin($value) {
		$this->setFieldValue('save_login',$value,'getSaveLogin');
	}

	public function getSaveLogin() {
		return $this->data['save_login'];
	}

	public function getShowOnlineState() {
		return $this->data['show_online_status'];
	}

	public function setShowOnlineState($value) {
		$this->setFieldValue('show_online_status',$value,'getShowOnlineState');
	}

	/**
	 */
	public function getContact() { #{{{
		return new ContactlistWrapper($this);
	} # }}}

	public function isFriend(&$userId) {
		$user = ResourceCache()->getUser($userId);
		if ($this->getAllianceId() > 0) {
			if ($this->getAllianceId() == $user->getAllianceId()) {
				return TRUE;
			}
			if ($this->getAlliance()->hasFriendlyRelation($user->getAllianceId())) {
				return TRUE;
			}
		}
		if (Contactlist::isFriendlyContact($this->getId(),$userId)) {
			return TRUE;
		}
		return FALSE;
	}

	public function isAdmin() {
		return isAdmin($this->getId());
	}

	public function getMapType() {
		return $this->data['maptype'];
	}

	public function setMapType($value) {
		$this->setFieldValue('maptype',$value,'getMapType');
	}

	public function getSessionData() {
		return $this->data['sessiondata'];
	}

	public function setSessionData($value) {
		$this->setFieldValue('sessiondata',$value,'getSessionData');
		$this->sessiondata = NULL;
	}

	private $sessiondata = NULL;

	public function getSessionDataUnserialized() {
		if ($this->sessiondata === NULL) {
			$this->sessiondata = unserialize($this->getSessionData());
			if (!is_array($this->sessiondata)) {
				$this->sessiondata = array();
			}
		}
		return $this->sessiondata;
	}

	/**
	 */
	public function isCurrentUser() { #{{{
		return $this->getId() == currentUser()->getId();
	} # }}}

	/**
	 */
	public function isContactable() { #{{{
		return !isSystemUser($this->getId());
	} # }}}

	private $free_crew_count = NULL;

	/**
	 */
	public function getFreeCrewCount() { #{{{
		if ($this->free_crew_count === NULL) {
			$this->free_crew_count = Crew::countInstances('WHERE user_id='.$this->getId().' AND id NOT IN (select crew_id FROM stu_ships_crew where user_id='.$this->getId().')');
		}
		return $this->free_crew_count;
	} # }}}

	/**
	 */
	public function setFreeCrewCount($value) { #{{{
		$this->free_crew_count = $value;
	} # }}}

	private $crew_count_debris = NULL;

	/**
	 */
	public function getCrewCountDebris() { #{{{
		if ($this->crew_count_debris === NULL) {
			$this->crew_count_debris = Crew::countInstances('WHERE user_id='.$this->getId().' AND id IN (SELECT crew_id FROM stu_ships_crew where ships_id IN (SELECT id FROM stu_ships where rumps_id IN (SELECT id FROM stu_rumps WHERE category_id='.SHIP_CATEGORY_DEBRISFIELD.')))');
		}
		return $this->crew_count_debris;
	} # }}}

	/**
	 */
	public function getTrainableCrewCountMax() { #{{{
		return ceil($this->getGlobalCrewLimit()/10);
	} # }}}

	private $global_crew_limit = NULL;

	/**
	 */
	public function getGlobalCrewLimit() { #{{{
		if ($this->global_crew_limit === NULL) {
			foreach (Colony::getListBy('user_id='.$this->getId()) as $key => $colony) {
				$this->global_crew_limit += $colony->getCrewLimit();
			}
		}
		return $this->global_crew_limit;
	} # }}}

	private $used_crew_count = NULL;

	/**
	 */
	public function getUsedCrewCount() { #{{{
		if ($this->used_crew_count === NULL) {
			$this->used_crew_count = ShipCrew::countInstances('WHERE user_id='.$this->getId());
		}
		return $this->used_crew_count;
	} # }}}

	/**
	 */
	public function getCrewLeftCount() { #{{{
		return max(0,$this->getGlobalCrewLimit()-$this->getUsedCrewCount()-$this->getFreeCrewCount()-$this->getInTrainingCrewCount());
	} # }}}

	private $crew_in_training = NULL;

	/**
	 */
	public function getInTrainingCrewCount() { #{{{
		if ($this->crew_in_training === NULL) {
			// @todo refactor
			global $container;

			$this->crew_in_training = $container->get(CrewTrainingRepositoryInterface::class)->getCountByUser((int) $this->getId());
		}
		return $this->crew_in_training;
	} # }}}

	public function generatePasswordToken() {
		$tok = sha1(time().$this->getLogin());
		$this->setPasswordToken($tok);
		$this->save();
		return $tok;
	}

	public function setPasswordToken($value) {
		$this->setFieldValue('password_token',$value,'getPasswordToken');
	}

	public function getPasswordToken() {
		return $this->data['password_token'];
	}
}

class User extends UserData {

	const USER_ACTIVE = 1;

	private const USER_IDLE_TIME = 120960000;

	function __construct($id=0) {
		$data = DB()->query("SELECT * FROM ".parent::tablename." WHERE id=".intval($id),4);
		if ($data == 0) {
			new ObjectNotFoundException($id);
		}
		parent::__construct($data);
	}

	static function getUserById($id=0) {
		$data = DB()->query("SELECT * FROM ".parent::tablename." WHERE id=".intval($id),4);
		if ($data == 0) {
			return FALSE;
		}
		return new UserData($data);
	}

	static function getByLogin($login) {
		$data = DB()->query("SELECT * FROM ".parent::tablename." WHERE login='".dbSafe($login)."' LIMIT 1",4);
		if ($data == 0) {
			return FALSE;
		}
		return new UserData($data);
	}

	static function getByEmail($email) {
		$data = DB()->query("SELECT * FROM ".parent::tablename." WHERE email='".dbSafe($email)."' LIMIT 1",4);
		if ($data == 0) {
			return FALSE;
		}
		return new UserData($data);
	}

	static function getListBy($sql) {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".parent::tablename." ".$sql);
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[$data['id']] = new UserData($data);
		}
		return $ret;
	}

	/**
	 */
	public static function getUserListIdle() { #{{{
		// XXX stub. we have to look at several conditions here
		return self::getListBy('WHERE id>100 AND id NOT IN ('.join(',',getAdminUserIds()).') AND lastaction<'.(time()-static::USER_IDLE_TIME));
	} # }}}

	public static function getUserListReset() {
		return self::getListBy('WHERE id>100');
	}

	public static function getByPasswordResetToken($token) {
		$data = DB()->query("SELECT * FROM ".parent::tablename." WHERE password_token='".dbSafe($token)."' LIMIT 1",4);
		if ($data == 0) {
			return FALSE;
		}
		return new UserData($data);
	}

	public static function getAmountByFaction(int $factionId): int
	{
		return (int) static::getCount('stu_user','race='.$factionId);
	}
}
?>
