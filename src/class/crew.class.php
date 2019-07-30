<?php

class CrewData extends BaseTable {

	protected $tablename = 'stu_crew';
	const tablename = 'stu_crew';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getGender() {
		return $this->data['gender'];
	}

	public function setGender($value) {
		$this->setFieldValue('gender',$value,'getGender');
	}

	public function getGenderShort() {
		if ($this->getGender() == CREW_GENDER_MALE) {
			return 'm';
		}
		return 'w';
	}

	public function getType() {
		return $this->data['type'];
	}

	public function setType($value) {
		$this->setFieldValue('type',$value,'getType');
	}

	public function getName() {
		return stripslashes(decodeString($this->getNameRaw()));
	}

	public function getNameRaw() {
		return $this->data['name'];
	}

	public function setName($value) {
		$this->setFieldValue('name',encodeString($value),'getNameRaw');
	}

	public function getUserId() {
		return $this->data['user_id'];
	}

	public function setUserId($value) {
		$this->setFieldValue('user_id',$value,'getUserId');
	}

	public function getTypeDescription() {
		switch ($this->getType()) {
			case CREW_TYPE_CREWMAN:
				return "Crewman";
			case CREW_TYPE_SECURITY:
				return "Sicherheit";
			case CREW_TYPE_SCIENCE:
				return "Wissenschaft";
			case CREW_TYPE_TECHNICAL:
				return "Technik";
			case CREW_TYPE_NAVIGATION:
				return "Navigation";
			case CREW_TYPE_COMMAND:
				return "Kommando";
		}
	}

	public function getGenderDescription() {
		switch ($this->getGender()) {
			case CREW_GENDER_MALE:
				return "Männlich";
			case CREW_GENDER_FEMALE:
				return "Weiblich";
		}
	}

	public function getRaceId() {
		return $this->data['race_id'];
	}

	public function setRaceId($value) {
		$this->setFieldValue('race_id',$value,'getRaceId');
	}

	public function getRace() {
		return CrewRaces::getById($this->getRaceId());
	}

	public function ownedByCurrentUser() {
		return $this->getUserId() == currentUser()->getId();
	}

	/**
	 */
	public function deepDelete() { #{{{
		/*
		foreach ($this->getProperties() as $key => $obj) {
			$obj->deepDelete();
		}
		*/
		$this->deleteFromDatabase();
	} # }}}

}
class Crew extends CrewData {

	function __construct($crewId=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$crewId." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($crewId);
		}
		return parent::__construct($result);
	}

	static public function getById($crewId) {
		return ResourceCache()->getObject('crew',$crewId);
	}

	/**
	 */
	static function getObjectsBy($sql='') { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' '.$sql);
		return self::_getList($result,'CrewData');
	} # }}}

	/**
	 */
	static function countInstances($qry="") { #{{{
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." ".$qry,1);
	} # }}}

	/**
	 */
	static function getFreeCrewByTypeAndUser($user_id,$type_id=FALSE) { #{{{
		$typeqry = '';
		if ($type_id) {
			$typeqry = 'AND type='.$type_id;
		}
		$result = DB()->query('SELECT * FROM '.self::tablename.' WHERE user_id='.$user_id.' '.$typeqry.' AND id NOT IN (select crew_id FROM stu_ships_crew where user_id='.$user_id.') LIMIT 1',4);
		if ($result == 0) {
			return FALSE;
		}
		return new CrewData($result);
	} # }}}

	/**
	 */
	static function create($userId) { #{{{
		$crew = new CrewData;
		$crew->setUserId($userId);
		$crew->setName('Crew');
		$crew->setRaceId(CrewRaces::getRandomRace(ResourceCache()->getObject(CACHE_USER,$userId)->getFaction()));
		$crew->setGender(CrewRaces::getRandomGenderByRace($crew->getRaceId()));
		$crew->setType(CREW_TYPE_CREWMAN);
		$crew->save();
		return $crew;
	} # }}}

}
?>
