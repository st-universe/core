<?php

use Stu\Orm\Repository\BuildingUpgradeCostRepositoryInterface;

class BuildingUpgradeData extends BaseTable {

	protected $tablename = 'stu_buildings_upgrades';
	const tablename = 'stu_buildings_upgrades';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	public function getUpgradeFrom() {
		return $this->data['upgrade_from'];
	}

	public function setUpgradeFrom($value) {
		$this->setFieldValue('upgrade_from',$value,'getUpgradeFrom');
	}

	public function getUpgradeTo() {
		return $this->data['upgrade_to'];
	}

	public function setUpgradeTo($value) {
		$this->setFieldValue('upgrade_to',$value,'getUpgradeTo');
	}

	public function getResearchId() {
		return $this->data['research_id'];
	}

	public function setResearchId($value) {
		$this->setFieldValue('research_id',$value,'getResearchId');
	}

	public function getDescription() {
		return $this->data['description'];
	}

	public function setDescription($value) {
		$this->setFieldValue('description',$value,'getDescription');
	}

	public function getBuilding() {
		return ResourceCache()->getObject("building",$this->getUpgradeTo());
	}

	/**
	 */
	public function getCost() { #{{{
		// @todo inject
		global $container;

		return $container->get(BuildingUpgradeCostRepositoryInterface::class)->getByBuildingUpgradeId(
			$this->getId()
		);
	} # }}}

	/**
	 */
	public function setEnergyCost($value) { # {{{
		$this->setFieldValue('energy_cost',$value,'getEnergyCost');
	} # }}}

	/**
	 */
	public function getEnergyCost() { # {{{
		return $this->data['energy_cost'];
	} # }}}

}

class BuildingUpgrade extends BuildingUpgradeData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".intval($id)." LIMIT 1",4);
		if ($result == 0) {
			throw new ObjectNotFoundException($id);
		}
		$this->data = &$result;
	}

	static public function getObjectsBy($qry) {
		$result = DB()->query("SELECT * FROM stu_buildings_upgrades WHERE ".$qry);
		return self::_getList($result,'BuildingUpgradeData');
	}

	static public function getObjectsBySource($buildingId=0) {
		return self::getObjectsBy('upgrade_from='.intval($buildingId).' AND (research_id=0 OR research_id IN (SELECT research_id FROM stu_researched WHERE user_id='.currentUser()->getId().' AND aktiv=0))');
	}
}
?>
