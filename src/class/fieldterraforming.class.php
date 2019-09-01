<?php

use Stu\Orm\Entity\TerraformingInterface;
use Stu\Orm\Repository\TerraformingRepositoryInterface;

class FieldTerraformingData extends BaseTable {

	protected $tablename = 'stu_colonies_terraforming';
	const tablename = 'stu_colonies_terraforming';
	
	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getFinishDate() {
		return $this->data['finished'];
	}

	function setFinishDate($value) {
		$this->data['finished'] = $value;
		$this->addUpdateField('finished','getFinishDate');
	}

	function getFinishDateDisplay() {
		return parseDateTime($this->getFinishDate()+60);
	}

	function getColonyId() {
		return $this->data['colonies_id'];
	}

	function setColonyId($value) {
		$this->data['colonies_id'] = $value;
		$this->addUpdateField('colonies_id','getColonyId');
	}

	function getFieldId() {
		return $this->data['field_id'];
	}

	function setFieldId($value) {
		$this->data['field_id'] = $value;
		$this->addUpdateField('field_id','getFieldId');
	}

	function getTerraformingId() {
		return $this->data['terraforming_id'];
	}

	function setTerraformingId($value) {
		$this->data['terraforming_id'] = $value;
		$this->addUpdateField('terraforming_id','getTerraformingId');
	}

	private $terraforming = NULL;

	/**
	 * @return TerraformingInterface
	 */
	function getTerraforming() {
		if ($this->terraforming === NULL) {
			// @todo refactor
			global $container;

			$this->terraforming = $container->get(TerraformingRepositoryInterface::class)->find((int) $this->getTerraformingId());
		}
		return $this->terraforming;
	}

	private $field = NULL;

	function getField() {
		if ($this->field === NULL) {
			$this->field = new Colfields($this->getFieldId());
		}
		return $this->field;
	}

	private $colony = NULL;

	function getColony() {
		if ($this->colony === NULL) {
			$this->colony = new Colony($this->getColonyId());
		}
		return $this->colony;
	}

	public function getProgress() {
		$start = $this->getFinishDate()-$this->getTerraforming()->getDuration();
		return time()-$start;
	}

}
class FieldTerraforming extends FieldTerraformingData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".$this->getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getByColonyField($colonyId,$fieldId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE colonies_id=".intval($colonyId)." AND field_id=".intval($fieldId),4);
		if ($result == 0) {
			return FALSE;
		}
		return new FieldTerraformingData($result);
	}

	static function getFinishedJobs() {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE finished<=".time());
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new FieldTerraformingData($data);
		}
		return $ret;
	}

	static function getUnFinishedJobsbyUser($userId=0) {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE colonies_id IN (SELECT id FROM stu_colonies WHERE user_id=".intval($userId).") AND finished>".time()." ORDER BY finished");
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new FieldTerraformingData($data);
		}
		return $ret;
	}

	static function truncate($colonyId=0) {
		DB()->query("DELETE FROM ".self::tablename." WHERE colonies_id=".$colonyId);
	}
}
?>
