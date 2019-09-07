<?php

use Stu\Module\Building\BuildingFunctionTypeEnum;
use Stu\Orm\Entity\BuildingCostInterface;
use Stu\Orm\Entity\BuildingFunctionInterface;
use Stu\Orm\Entity\BuildingGoodInterface;
use Stu\Orm\Entity\PlanetFieldTypeBuildingInterface;
use Stu\Orm\Repository\BuildingCostRepositoryInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\BuildingGoodRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldTypeBuildingRepositoryInterface;

class BuildingData extends BaseTable {

	const tablename = 'stu_buildings';
	protected $tablename = 'stu_buildings';

	function __construct($result) {
		$this->data = $result;
	}
	
	function getName() {
		return $this->data['name'];
	}

	function getBuildingType() {
		// return 0 for now
		return 0;
	}

	function getStorage() {
		return $this->data['lager'];
	}

	/**
	 */
	public function setEpsCost($value) { # {{{
		$this->setFieldValue('eps_cost',$value,'getEpsCost');
	} # }}}

	/**
	 */
	public function getEpsCost() { # {{{
		return $this->data['eps_cost'];
	} # }}}


	function getEpsStorage() {
		return $this->data['eps'];
	}

	function getEpsProduction() {
		return $this->data['eps_proc'];
	}

	function getEpsProductionDisplay() {
		if ($this->getEpsProduction() < 0) {
			return $this->getEpsProduction();
		}
		return '+'.$this->getEpsProduction();
	}

	public function getEpsProductionCss() {
		if ($this->getEpsProduction() < 0) {
			return 'negative';
		}
		if ($this->getEpsProduction() > 0) {
			return 'positive';
		}
	}

	function getHousing() {
		return $this->data['bev_pro'];
	}

	function getWorkers() {
		return $this->data['bev_use'];
	}

	function getIntegrity() {
		return $this->data['integrity'];
	}

	function getResearchId() {
		return $this->data['research_id'];
	}

	function isViewable() {
		return $this->data['view'];
	}

	function getShieldpoints() {
		return $this->data['schilde'];
	}

	function getConstructionTime() {
		return $this->data['buildtime'];
	}

	function getLimit() {
		return $this->data['blimit'];
	}

	function hasLimit() {
		return $this->getLimit() > 0;
	}

	function getLimitColony() {
		return $this->data['bclimit'];
	}

	function hasLimitColony() {
		return $this->getLimitColony() > 0;
	}

	function isActivateable() {
		return $this->data['is_activateable'];
	}

	function needsSpaceCenter() {
		return $this->data['needs_rbf'];
	}

	private $buildfields = NULL;

	function getBuildableFields() {
		if ($this->buildfields === NULL) {
			$this->buildfields = array();
			// @todo refactor
			global $container;

			$this->buildfields = array_map(
				function (PlanetFieldTypeBuildingInterface $fieldTypeBuilding): int {
					return $fieldTypeBuilding->getFieldTypeId();
				},
				$container->get(PlanetFieldTypeBuildingRepositoryInterface::class)->getByBuilding((int) $this->getId())
			);
		}
		return $this->buildfields;
	}

	function getBuildTime() {
		return $this->data['buildtime'];
	}

	private $costs = NULL;

	/**
	 * @return BuildingCostInterface[]
	 */
	function getCosts() {
		if ($this->costs === NULL) {
			// @todo refactor
			global $container;

			$this->costs = $container->get(BuildingCostRepositoryInterface::class)->getByBuilding((int) $this->getId());
		}
		return $this->costs;
	}

	private $goods = NULL;

	/**
	 * @return BuildingGoodInterface[]
	 */
	function getGoods() {
		if ($this->goods === NULL) {
			// @todo refactor
			global $container;

			$this->goods = $container->get(BuildingGoodRepositoryInterface::class)->getByBuilding((int) $this->getId());
		}
		return $this->goods;
	}

	private $functions = NULL;

	/**
	 * @return BuildingFunctionInterface[]
	 */
	public function getFunctions(): array {
		if ($this->functions === NULL) {
			// @todo refactor
			global $container;

			$this->functions = $container->get(BuildingFunctionRepositoryInterface::class)->getByBuilding((int) $this->getId());
		}
		return $this->functions;
	}

	private $functionList;

	private function getFunctionList(): array
	{
		if ($this->functionList === null) {
			$this->functionList = [];
			foreach ($this->getFunctions() as $function) {
				$this->functionList[$function->getFunction()] = $function;
			}
		}
		return $this->functionList;
	}

	public function hasFunction($func) {
		return array_key_exists($func,$this->getFunctionList());
	}

	public function getFunction($func) {
		return $this->getFunctionList()[$func];
	}

	public function isColonyCentral() {
		return $this->hasFunction(BUILDING_FUNCTION_CENTRAL);
	}

	public function isAcademy() {
		return $this->hasFunction(BUILDING_FUNCTION_ACADEMY);
	}

	public function isShipyard() {
		foreach ($this->getFunctions() as $func) {
			if (in_array($func->getFunction(), BuildingFunctionTypeEnum::getShipyardOptions())) {
				return $func;
			}
		}
		return FALSE;
	}

	/**
	 */
	public function postDeactivation(ColonyData $colony) { #{{{
		if (($func=$this->isShipyard())) {
			// @todo refactor
			global $container;

			$container->get(ColonyShipQueueRepositoryInterface::class)->stopQueueByColonyAndBuildingFunction(
				(int) $colony->getId(),
				$func->getFunction()
			);
		}
	} # }}}

	/**
	 */
	public function postActivation(ColonyData $colony) { #{{{
		if (($func=$this->isShipyard())) {
			// @todo refactor
			global $container;

			$container->get(ColonyShipQueueRepositoryInterface::class)->restartQueueByColonyAndBuildingFunction(
				(int) $colony->getId(),
				$func->getFunction()
			);
		}
	} # }}}

	/**
	 */
	public function isAirfield() { #{{{
		return $this->hasFunction(BUILDING_FUNCTION_AIRFIELD);
	} # }}}

	/**
	 */
	public function isFighterShipyard() { #{{{
		return $this->hasFunction(BUILDING_FUNCTION_FIGHTER_SHIPYARD);
	} # }}}

	/**
	 */
	public function isModuleFab() { #{{{
		foreach ($this->getFunctions() as $func) {
			if (in_array($func->getFunction(), BuildingFunctionTypeEnum::getModuleFabOptions())) {
				return $func;
			}
		}
		return FALSE;
	} # }}}

	/**
	 */
	public function isTorpedoFab() { #{{{
		return $this->hasFunction(BUILDING_FUNCTION_TORPEDO_FAB);
	} # }}}

	public function getFunctionString() {
		$func = array();
		if ($this->isShipyard()) {
			$func[] = "Schiffbau";
		}
		if ($this->isAcademy()) {
			$func[] = "Crewausbildung";
		}
		if ($this->isAirfield()) {
			$func[] = "Shuttlebau";
		}
		return implode(",",$func);
	}

	/**
	 */
	public function isRemoveAble() { #{{{
		if ($this->isColonyCentral()) {
			return FALSE;
		}
		return TRUE;
	} # }}}

	/**
	 */
	public function onDestruction($colony_id) { #{{{
		// @todo refactor
		global $container;
		// XXX we need a registry in here
		if ($this->isAcademy()) {
			$container->get(CrewTrainingRepositoryInterface::class)->truncateByColony((int) $colony_id);
		}
		if (($func = $this->isShipyard())) {
		    $container->get(ColonyShipQueueRepositoryInterface::class)->truncateByColonyAndBuildingFunction(
			    (int) $colony_id,
			    $func->getFunction()
		    );
		}
	} # }}}

}
class Building extends BuildingData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".intval($id)." LIMIT 1",4);
		if ($result == 0) {
			throw new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getBuildingMenuList($userId, $colonyId,$type,$offset=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE bm_col=".intval($type)." AND view=1 AND (research_id=0 OR research_id IN (SELECT research_id
			FROM stu_researched WHERE user_id=".$userId." AND aktiv=0)) AND id IN (SELECT buildings_id FROM stu_field_build WHERE type IN 
			(SELECT type FROM stu_colonies_fielddata WHERE colonies_id=".$colonyId.")) GROUP BY id ORDER BY name LIMIT ".$offset.",".BUILDMENU_SCROLLOFFSET);
		return self::_getList($result,'BuildingData','id','building');
	}
}
?>
