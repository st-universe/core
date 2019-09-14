<?php

use Lib\AllianceMemberWrapper;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

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

	function getHomepage() {
		return $this->data['homepage'];
	}

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

	private $founder = NULL;

	function getFounder() {
		if ($this->founder === NULL) {
			// @todo refactor
			global $container;

			$this->founder = $container->get(AllianceJobRepositoryInterface::class)
				->getSingleResultByAllianceAndType(
					(int) $this->getId(),
					ALLIANCE_JOBS_FOUNDER
				);
		}
		return $this->founder;
	}

	private $successor = NULL;

	function getSuccessor() {
		if ($this->successor === NULL) {
			// @todo refactor
			global $container;

			$this->successor = $container->get(AllianceJobRepositoryInterface::class)
				->getSingleResultByAllianceAndType(
					(int) $this->getId(),
					ALLIANCE_JOBS_SUCCESSOR
				);
		}
		return $this->successor;
	}

	private $diplomatic = NULL;

	function getDiplomatic() {
		if ($this->diplomatic === NULL) {
			// @todo refactor
			global $container;

			$this->diplomatic = $container->get(AllianceJobRepositoryInterface::class)
				->getSingleResultByAllianceAndType(
					(int) $this->getId(),
					ALLIANCE_JOBS_DIPLOMATIC
				);

		}
		return $this->diplomatic;
	}

	private $members = NULL;

	/**
	 * @return AllianceMemberWrapper[]
	 */
	function getMembers() {
		if ($this->members === NULL) {
			foreach (User::getListBy('WHERE allys_id='.$this->getId()) as $user) {
				$this->members[$user->getId()] = new AllianceMemberWrapper($user,$this);
			}
		}
		return $this->members;
	}

	function setAcceptApplications($value) {
		$this->data['accept_applications'] = $value;
		$this->addUpdateField('accept_applications','getAcceptApplications');
	}

	function getAcceptApplications() {
		return $this->data['accept_applications'];
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
