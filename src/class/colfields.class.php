<?php

use Stu\Orm\Repository\BuildingUpgradeRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\TerraformingRepositoryInterface;

class ColfieldData extends BaseTable {

	protected $tablename = 'stu_colonies_fielddata';
	const tablename = 'stu_colonies_fielddata';
	
	function __construct(&$data = array()) {
		$this->data = $data;
	}

	private $buildmode = FALSE;

	function setBuildMode($value) {
		$this->buildmode = $value;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getColonyId() {
		return $this->data['colonies_id'];
	}

	public function setColonyId($value) {
		$this->data['colonies_id'] = $value;
		$this->addUpdateField('colonies_id','getColonyId');
	}

	function getFieldId() {
		return $this->data['field_id'];
	}

	public function setFieldId($value) {
		$this->data['field_id'] = $value;
		$this->addUpdateField('field_id','getFieldId');
	}

	function getFieldType() {
		return $this->data['type'];
	}

	public function getFieldTypeName() {
		return getFieldName($this->getFieldType());
	}

	function setFieldType($value) {
		$this->data['type'] = $value;
		$this->addUpdateField('type','getFieldType');
	}

	function getBuildingId() {
		return $this->data['buildings_id'];
	}

	function setBuildingId($value) {
		$this->data['buildings_id'] = $value;
		$this->addUpdateField('buildings_id','getBuildingId');
	}

	function setBuildtime($time) {
		$this->data['aktiv'] = time()+$time;
		$this->addUpdateField('aktiv','getBuildtime');
	}

	function setTerraformingId($value) {
		$this->data['terraforming_id'] = $value;
		$this->addUpdateField('terraforming_id','getTerraformingId');
	}

	function getTerraformingId() {
		return $this->data['terraforming_id'];
	}

	function getBuildtime() {
		return $this->data['aktiv'];
	}

	function isActive() {
		return $this->data['aktiv'] == 1;
	}

	function setActive($value) {
		$this->data['aktiv'] = $value;
		$this->addUpdateField('aktiv','isActive');
	}

	public function isActivateable() {
		if ($this->isInConstruction()) {
			return FALSE;
		}
		return $this->getBuilding()->isActivateable();
	}

	public function hasHighDamage() {
		if (!$this->isDamaged()) {
			return FALSE;
		}
		if (round((100/$this->getBuilding()->getIntegrity())*$this->getIntegrity()) < 50) {
			return TRUE;
		}
		return FALSE;
	}

	function isInConstruction() {
		return $this->data['aktiv'] > 1;
	}

	function hasBuilding() {
		return $this->getBuildingId() > 0;
	}

	function getCssClass() {
		if ($this->buildmode === TRUE) {
			return 'cfb';
		}
		if ($this->isActive()) {
			return 'cfa';
		}
		return 'cfd';
	}

	function getBuildingState() {
		if ($this->isInConstruction()) {
			return 'b';
		}
		return 'a';
	}

	/**
	 * @return Building
	 */
	function getBuilding() {
		return ResourceCache()->getObject("building",$this->getBuildingId());
	}

	function getIntegrity() {
		return $this->data['integrity'];
	}

	function setIntegrity($value) {
		$this->data['integrity'] = $value;
		$this->addUpdateField('integrity','getIntegrity');
	}

	function isDamaged() {
		if (!$this->hasBuilding()) {
			return FALSE;
		}
		if ($this->isInConstruction()) {
			return FALSE;
		}
		return $this->getIntegrity() != $this->getBuilding()->getIntegrity();
	}

	function clearBuilding() {
		$this->getBuilding()->onDestruction($this->getColonyId());
		$this->setBuildingId(0);
		$this->setIntegrity(0);
		$this->setActive(0);
	}

	private $colony = NULL;

	function getColony() {
		if ($this->colony === NULL) {
			$this->colony = new Colony($this->getColonyId());
		}
		return $this->colony;
	}

	private $terraforming = NULL;

	function hasTerraforming() {
		return $this->getTerraformingId() != 0;
	}

	public function getTerraforming() {
		if ($this->terraforming === NULL) {
			// @todo refactor
			global $container;

			$this->terraforming = $container->get(ColonyTerraformingRepositoryInterface::class)->getByColonyAndField(
				(int) $this->getColonyId(),
				(int) $this->getId()
			);
		}
		return $this->terraforming;
	}

	private $terraformingopts = NULL;

	function getTerraformingOptions() {
		if ($this->terraformingopts === NULL) {
			// @todo refactor
			global $container;

            $this->terraformingopts = $container->get(TerraformingRepositoryInterface::class)->getBySourceFieldType(
	            (int) $this->getFieldType()
            );
		}
		return $this->terraformingopts;
	}

	public function getTitleString() {
		if (!$this->hasBuilding()) {
			if ($this->hasTerraforming()) {
				return $this->getTerraforming()->getTerraforming()->getDescription()." läuft bis ".parseDateTime($this->getTerraforming()->getFinishDate());
			}
			return $this->getFieldTypeName();
		}
		if ($this->isinConstruction()) {
			return sprintf(
				_('In Bau: %s auf %s - Fertigstellung: %s'),
				$this->getBuilding()->getName(),
				$this->getFieldTypeName(),
				date('d.m.Y H:i', $this->getBuildtime())
			);
		}
		if (!$this->isActivateable()) {
			return $this->getBuilding()->getName()." auf ".$this->getFieldTypeName();	
		}
		if ($this->isActive()) {
			return $this->getBuilding()->getName()." (aktiviert) auf ".$this->getFieldTypeName();
		}
		return $this->getBuilding()->getName()." (deaktiviert) auf ".$this->getFieldTypeName();
	}

	public function getBuildProgress() {
		$start = $this->getBuildtime()-$this->getBuilding()->getBuildTime();
		return time()-$start;
	}

	public function getOverlayWidth() {
		$perc = getPercentage($this->getBuildProgress(),$this->getBuilding()->getBuildtime());
		return round((40/100)*$perc);
	}

	public function getPictureType() {
		return $this->getBuildingId()."/".$this->getBuilding()->getBuildingType().$this->getBuildingState();
	}

	private $upgrades = NULL;

	public function getPossibleUpgrades() {
		if ($this->isInConstruction() || $this->getBuildingId() == 0) {
			return [];
		}
		if ($this->upgrades === NULL) {
			// @todo refactor
			global $container;
			$this->upgrades = $container
				->get(BuildingUpgradeRepositoryInterface::class)
				->getByBuilding((int) $this->getBuildingId(), (int) $this->getColony()->getUserId());
		}
		return $this->upgrades;
	}

	/**
	 */
	public function isColonizeAble() { #{{{
		return in_array($this->getFieldType(),$this->getColony()->getPlanetType()->getColonizeableFields());
	} # }}}

	/**
	 */
	public function hasUpgradeOrTerraformingOption() { #{{{
		return (!$this->isInConstruction() && count($this->getPossibleUpgrades()) > 0) || (count($this->getTerraformingOptions()) > 0 && !$this->hasBuilding());
	} # }}}

}
class Colfields extends ColfieldData {

	private const COLONY_SEPERATOR_DEFAULT = 10;
	private const COLONY_SEPERATOR_MOON = 7;

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".$this->getTable()." WHERE id=".intval($id)." LIMIT 1",4);
		if ($result == 0) {
			throw new ObjectNotFoundException($id);
		}
		parent::__construct($result);
	}

	static function getByColonyField($fieldId,$colonyId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE field_id=".intval($fieldId)." AND colonies_id=".intval($colonyId)." LIMIT 1",4);
		if ($result == 0) {
			throw new ObjectNotFoundException($fieldId);
		}
		return new ColfieldData($result);
	}

	static function getFieldsBy($where,$isMoon=FALSE) {
		$sep = static::COLONY_SEPERATOR_DEFAULT;
		if ($isMoon) {
			$sep = static::COLONY_SEPERATOR_MOON;
		}
		$result = DB()->query("SELECT a.*,b.name FROM ".self::tablename." as a LEFT JOIN stu_buildings as b ON b.id=a.buildings_id WHERE ".$where." ORDER BY a.field_id ASC LIMIT 100");
		$ret = array();
		if (request::getInt('bid')) {
			$building = new Building(request::getInt('bid'));
		}
		while($data = mysqli_fetch_assoc($result)) {
			$val = new ColfieldData($data);
			if (request::getInt('bid') && $val->getTerraformingId() == 0 && in_array($val->getFieldType(),$building->getBuildableFields())) {
				$val->setBuildMode(TRUE);
			}
			$ret[] = $val;
		}
		return $ret;
	}

	static function getListBy($where) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE ".$where);
		return self::_getList($result,'ColfieldData','field_id');
	}

	static function getBy($qry) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE ".$qry." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new ColfieldData($result);
	}

	static function countInstances($qry) {
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." WHERE ".$qry,1);
	}

	static function getUnFinishedBuildingJobsByUser($userId=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE aktiv>1 AND colonies_id IN (SELECT id FROM stu_colonies WHERE user_id=".intval($userId).") ORDER BY aktiv");
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new ColfieldData($data);
		}
		return $ret;
	}

	static public function getFieldsByBuildingFunction($colonyId,$func,$active=FALSE) {
		if (is_array($func)) {
			$qry = '`function` IN ('.join(',',$func).')';
		} else {
			$qry = '`function`='.$func;
		}
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE colonies_id=".intval($colonyId)." AND aktiv".($active ? '=1' : '<2')." AND buildings_id IN (SELECT buildings_id FROM stu_buildings_functions WHERE `function`=".intval($func).")");
		return self::_getList($result,'ColFieldData');
	}

	/**
	 */
	static function insertColonyFields($colonyId,&$fields) { #{{{
		foreach ($fields as $key => $fieldtype) {
			$field = new ColFieldData;
			$field->setColonyId($colonyId);
			$field->setFieldId($key);
			$field->setFieldType($fieldtype);
			$field->save();
		}
	} # }}}

	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' WHERE '.$sql);
	} # }}}

}
?>
