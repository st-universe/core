<?php

class ResearchData extends BaseTable {

	protected $tablename = 'stu_research';
	const tablename = 'stu_research';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getName() {
		return $this->data['name'];
	}

	public function setName($value) {
		$this->setFieldValue('name',$value,'getName');
	}

	public function getDescription() {
		return $this->data['description'];
	}

	public function setDescription($value) {
		$this->setFieldValue('description',$value,'getDescription');
	}

	public function getSort() {
		return $this->data['sort'];
	}

	public function setSort($value) {
		$this->setFieldValue('sort',$value,'getSort');
	}

	public function getRumpId() {
		return $this->data['rumps_id'];
	}

	public function setRumpId($value) {
		$this->setFieldValue('rumps_id',$value,'getRumpId');
	}

	/**
	 */
	public function setDatabaseEntries($value) { # {{{
		$this->setFieldValue('database_entries',$value,'getDatabaseEntries');
	} # }}}

	/**
	 */
	public function getDatabaseEntries() { # {{{
		return $this->data['database_entries'];
	} # }}}

	/**
	 */
	public function getGood() { #{{{
		return ResourceCache()->getObject('good',$this->getGoodId());
	} # }}}

	/**
	 */
	public function setGoodId($value) { # {{{
		$this->setFieldValue('good_id',$value,'getGoodId');
	} # }}}

	/**
	 */
	public function getGoodId() { # {{{
		return $this->data['good_id'];
	} # }}}
	
	public function getPoints() {
		return $this->data['points'];
	}

	public function setPoints($value) {
		$this->setFieldValue('points',$value,'getPoints');
	}

	private $state = NULL;

	public function getResearchState() {
		if ($this->state === NULL) {
			$this->state = ResearchUser::getByResearch($this->getId(),currentUser()->getId());
		}
		return $this->state;
	}

	public function getDescriptionFormatted() {
		return nl2br($this->getDescription());
	}

	private $excludes = NULL;

	public function getExcludes() {
		if ($this->excludes === NULL) {
			$this->excludes = ResearchDependency::getExcludesByResearch($this->getId());
		}
		return $this->excludes;
	}

	public function hasExcludes() {
		return count($this->getExcludes()) > 0;
	}

	private $positiveDependencies = NULL;

	public function getPositiveDependencies() {
		if ($this->positiveDependencies === NULL) {
			$this->positiveDependencies = ResearchDependency::getPositiveDependenciesByResearch($this->getId());
		}
		return $this->positiveDependencies;
	}

	public function hasPositiveDependencies() {
		return count($this->getPositiveDependencies()) > 0;
	}

	public function getDonePoints() {
		return $this->getPoints()-$this->getResearchState()->getActive();
	}

	/**
	 */
	public function setUpperPlanetlimit($value) { # {{{
		$this->setFieldValue('upper_planetlimit',$value,'getUpperPlanetlimit');
	} # }}}

	/**
	 */
	public function getUpperPlanetlimit() { # {{{
		return $this->data['upper_planetlimit'];
	} # }}}

	/**
	 */
	public function setUpperMoonlimit($value) { # {{{
		$this->setFieldValue('upper_moonlimit',$value,'getUpperMoonlimit');
	} # }}}

	/**
	 */
	public function getUpperMoonlimit() { # {{{
		return $this->data['upper_moonlimit'];
	} # }}}

	/**
	 */
	public function isStartResearch() { #{{{
		return in_array($this->getId(),getDefaultTechs());
	} # }}}

}
class Research extends ResearchData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($crewId);
		}
		return parent::__construct($result);
	}

	static function getListByUser($userId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id NOT IN (SELECT research_id FROM stu_researched WHERE user_id=".$userId." AND aktiv=0) ORDER BY sort");
		return self::_getList($result,'ResearchData');
	}
}
?>
