<?php

class CrewRacesData extends BaseTable {

	protected $tablename = 'stu_crew_race';
	const tablename = 'stu_crew_race';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getFactionId() {
		return $this->data['faction_id'];
	}

	public function setFactionId($value) {
		$this->setFieldValue('faction_id',$value,'getFactionId');
	}

	public function getDescription() {
		return $this->data['description'];
	}

	public function setDescription($value) {
		$this->setFieldValue('description',$value,'getDescription');
	}

	public function getChance() {
		return $this->data['chance'];
	}

	public function setChange($value) {
		$this->setFieldValue('chance',$value,'getChance');
	}

	public function getPrefixA() {
		return $this->data['prefix_a'];
	}

	public function setPrefixA($value) {
		$this->setFieldValue('prefix_a',$value,'getPrefixA');
	}

	public function getPrefixB() {
		return $this->data['prefix_b'];
	}

	public function setPrefixB($value) {
		$this->setFieldValue('prefix_b',$value,'getPrefixB');
	}

	public function getDefine() {
		return $this->data['define'];
	}

	public function setDefine($value) {
		$this->setFieldValue('define',$value,'getDefine');
	}

	/**
	 */
	public function setMaleRatio($value) { # {{{
		$this->setFieldValue('maleratio',$value,'getMaleRatio');
	} # }}}

	/**
	 */
	public function getMaleRatio() { # {{{
		return $this->data['maleratio'];
	} # }}}
	
}
class CrewRaces extends CrewRacesData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static public function getById($id) {
		return ResourceCache()->getObject('crewraces',$id);
	}

	/**
	 */
	static function getObjectsBy($sql='') { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' '.$sql);
		return self::_getList($result,'CrewRacesData');
	} # }}}
}
