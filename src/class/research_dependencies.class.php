<?php

class ResearchDependencyData extends BaseTable {

	protected $tablename = 'stu_research_dependencies';
	const tablename = 'stu_research_dependencies';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getResearchId() {
		return $this->data['research_id'];
	}

	public function setResearchId($value) {
		$this->setFieldValue('research_id',$value,'getResearchId');
	}

	public function getDependOn() {
		return $this->data['depends_on'];
	}

	public function setDependOn($value) {
		$this->setFieldValue('depends_on',$value,'getDependOn');
	}

	public function getMode() {
		return $this->data['mode'];
	}

	public function setMode($value) {
		$this->setFieldValue('mode',$value,'getMode');
	}

        public function getResearch() {
                return ResourceCache()->getObject('research',$this->getResearchId());
        }

	public function getResearchDependOn() {
		return ResourceCache()->getObject('research',$this->getDependOn());
	}
}
class ResearchDependency extends ResearchDependencyData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getList() {
		$result = DB()->query(sprintf('SELECT * FROM %s WHERE mode!=%d',self::tablename,RESEARCH_MODE_EXCLUDE));
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[$data['research_id']][] = new ResearchDependencyData($data);
		}
		return $ret;
	}

	static public function getListExcludes() {
		$result = DB()->query(sprintf('SELECT * FROM %s WHERE mode=%d',self::tablename,RESEARCH_MODE_EXCLUDE));
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[$data['depends_on']][] = new ResearchDependencyData($data);
		}
		return $ret;
	}

	static function getExcludesByResearch($researchId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE mode=".RESEARCH_MODE_EXCLUDE." AND research_id=".$researchId);
		return self::_getList($result,'ResearchDependencyData');
	}

	static function getPositiveDependenciesByResearch($researchId) {
		$result = DB()->query("SELECT a.* FROM ".self::tablename." as a LEFT JOIN stu_research as b on b.id=a.research_id WHERE a.mode!=".RESEARCH_MODE_EXCLUDE." AND a.depends_on=".$researchId." GROUP BY b.name");
		return self::_getList($result,'ResearchDependencyData');
	}
}
?>
