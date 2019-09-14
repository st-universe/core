<?php

use Stu\Lib\ColonyEpsProductionPreviewWrapper;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\ColonyProductionPreviewWrapper;
use Stu\Lib\ColonyStorageGoodWrapper\ColonyStorageGoodWrapper;
use Stu\Module\Building\BuildingFunctionTypeEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyStorageInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\PlanetTypeInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetTypeRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;
use Stu\PlanetGenerator\PlanetGenerator;

class ColonyData extends BaseTable {

	public const PEOPLE_FOOD = 7;

	const tablename = 'stu_colonies';
	protected $tablename = 'stu_colonies';

	function __construct($data=array()) {
		$this->data = &$data;
	}
	
	function getId() {
		return $this->data['id'];
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	function ownedByCurrentUser() {
		return $this->getUserId() == currentUser()->getId();
	}

	function getColonyClass() {
		return $this->data['colonies_classes_id'];
	}

	/**
	 */
	public function setColonyClass($value) { #{{{
		$this->setFieldValue('colonies_classes_id',$value,'getColonyClass');
	} # }}}


	private $planettype = NULL;

	public function getPlanetType(): PlanetTypeInterface {
		if ($this->planettype === NULL) {
			// @todo refactor
			global $container;

			$this->planettype = $container->get(PlanetTypeRepositoryInterface::class)->find((int) $this->getColonyClass());
		}
		return $this->planettype;
	}

	function setName($value) {
		if ($value == $this->getName()) {
			return; 
		}
		$old = $this->getName();
		$value = strip_tags($value);
		$this->data['name'] = $value;
		if (strlen($this->getNameWithoutMarkup()) < 3) {
			$this->name = $old;
			return;
		}
		$this->addUpdateField('name','getName');
	}

	function getName() {
		return $this->data['name'];
	}

	function getNameWithoutMarkup() {
		return BBCode()->parse($this->getName())->getAsText();
	}
	function getEps() {
		return $this->data['eps'];
	}

	function setEps($value) {
		if ($value > $this->getMaxEps()) {
			$value = $this->getMaxEps();
		}
		$this->data['eps'] = $value;
		$this->addUpdateField('eps','getEps');
	}

	function lowerEps($value) {
		$this->setEps($this->getEps()-$value);
	}

	function upperEps($value) {
		$this->setEps($this->getEps()+$value);
	}

	function getMaxEps() {
		return $this->data['max_eps'];
	}

	function getMask() {
		return $this->data['mask'];
	}

	function setMask($value) {
		$this->data['mask'] = $value;
		$this->addUpdateField('mask','getMask');
	}

	function getMaxStorage() {
		return $this->data['max_storage'];
	}

	private $storagesum = NULL;

	function getStorageSum() {
		if ($this->storagesum === NULL) {
			$this->storagesum = DB()->query("SELECT SUM(count) FROM stu_colonies_storage WHERE colonies_id=".$this->getId(),1);
		}
		return $this->storagesum;
	}

	function setStorageSum($value) {
		$this->storagesum = $value;
	}

	function storagePlaceLeft() {
		return $this->getMaxStorage() > $this->getStorageSum();
	}

	public function lowerStorage($good_id,$count) {
	    $stor = $this->getStorage()[$good_id] ?? null;
		if ($stor === null) {
			return;
		}

		// @todo refactor
		global $container;
		$colonyStorageRepo = $container->get(ColonyStorageRepositoryInterface::class);

		if ($stor->getAmount() <= $count) {
		    $colonyStorageRepo->delete($stor);
			return;
		}
		$stor->setAmount($stor->getAmount() - $count);

		$colonyStorageRepo->save($stor);

		$this->storage = null;
	}

	public function upperStorage($good_id,$count) {
	    $stor = $this->getStorage()[$good_id] ?? null;

		// @todo refactor
		global $container;
		$colonyStorageRepo = $container->get(ColonyStorageRepositoryInterface::class);

		if ($stor === null) {
			/** @var CommodityInterface $commodity */
			$commodity = $container->get(CommodityRepositoryInterface::class)->find((int) $good_id);

			$stor = $colonyStorageRepo->prototype();
			$stor->setColonyId((int) $this->getId());
			$stor->setGood($commodity);
		}
		$stor->setAmount($stor->getAmount() + $count);

		$colonyStorageRepo->save($stor);

		$this->storage = null;
	}

	function isInSystem() {
		// true by default
		return TRUE;
	}

	function getPosX() {
		return $this->getSX();
	}

	function getPosY() {
		return $this->getSY();
	}

	/**
	 */
	public function setSX($value) { # {{{
		$this->setFieldValue('sx',$value,'getSX');
	} # }}}

	/**
	 */
	public function getSX() { # {{{
		return $this->data['sx'];
	} # }}}

	/**
	 */
	public function setSY($value) { # {{{
		$this->setFieldValue('sy',$value,'getSY');
	} # }}}

	/**
	 */
	public function getSY() { # {{{
		return $this->data['sy'];
	} # }}}

	function getSystemsId() {
		return $this->data['systems_id'];
	}

	/**
	 */
	public function setSystemsId($value) { #{{{
		$this->setFieldValue('systems_id',$value,'getSystemsId');
	} # }}}

	private $system = NULL;

	function getSystem() {
		if ($this->system === NULL) {
			// @todo refactor
			global $container;

			$this->system = $container->get(StarSystemRepositoryInterface::class)->find((int) $this->getSystemsId());
		}
		return $this->system;
	}

	private $epsproduction = NULL;

	function getEpsProduction() {
		if ($this->epsproduction === NULL) {
			$this->epsproduction = DB()->query("SELECT SUM(b.eps_proc) FROM stu_colonies_fielddata as a LEFT JOIN
					     stu_buildings as b ON b.id=a.buildings_id WHERE a.aktiv=1 AND a.colonies_id=".$this->getId(),1);
		}
		return $this->epsproduction;
	}

	function setEpsProduction($value) {
		$this->epsproduction = $value;
	}

	function getEpsProductionDisplay() {
		if ($this->getEpsProduction() > 0) {
			return '+'.$this->getEpsProduction();
		}
		return $this->getEpsProduction();
	}

	public function getEpsProductionCss() {
		if ($this->getEpsProduction() > 0) {
			return 'positive';
		}
		if ($this->getEpsProduction() < 0) {
			return 'negative';
		}
	}

	/**
	 */
	public function getEpsProductionForecast() { #{{{
		if ($this->getEps() + $this->getEpsProduction() < 0) {
			return 0;
		}
		if ($this->getEps() + $this->getEpsProduction() > $this->getMaxEps()) {
			return $this->getMaxEps();
		}
		return $this->getEps()+$this->getEpsProduction();
	} # }}}


	private $colfields = NULL;

	function getColonyFields() {
		if ($this->colfields === NULL) {
			$this->colfields = Colfields::getFieldsBy('colonies_id='.$this->getId(),$this->getPlanetType()->getIsMoon());
			if (count($this->colfields) == 0) {
				$this->updateColonySurface();
				$this->colfields = Colfields::getFieldsBy('colonies_id='.$this->getId(),$this->getPlanetType()->getIsMoon());
			}
		}
		return $this->colfields;
	}

	public function getSurfaceTileCssClass(): string {
		if ($this->getPlanetType()->getIsMoon()) {
			return 'moonSurfaceTiles';
		}
		return 'planetSurfaceTiles';
	}

    /**
     * @return ColonyStorageInterface[]
     */
    public function getBeamableStorage(): array
    {
        return array_filter(
            $this->getStorage(),
            function (ColonyStorageInterface $storage): bool {
                return $storage->getGood()->isBeamable() === true;
            }
        );
    }

	private $storage = NULL;

	/**
	 * @return ColonyStorageInterface[]
	 */
	function getStorage() {
		if ($this->storage === NULL) {
		    // @todo refactor
			global $container;

			$this->storage = $container->get(ColonyStorageRepositoryInterface::class)->getByColony((int) $this->getId());
		}
		return $this->storage;
	}

	public function resetStorage() {
		$this->storage = NULL;
	}

	private $productionRaw = NULL;
	private $production = NULL;

	/**
	 * @return ColonyProduction[]
	 */
	public function getProductionRaw() {
		if ($this->productionRaw === NULL) {
			$this->productionRaw = ColonyProduction::getProductionByColony($this);
		}
		return $this->productionRaw;
	}

	/**
	 */
	public function setProductionRaw($array) { #{{{
		$this->productionRaw = $array;
	} # }}}

	public function getProduction() {
		if ($this->production === NULL) {
			$this->production = $this->getProductionRaw();
			if (array_key_exists(CommodityTypeEnum::GOOD_FOOD,$this->production)) {
				if ($this->production[CommodityTypeEnum::GOOD_FOOD]->getProduction()-$this->getBevFood() == 0) {
					unset($this->production[CommodityTypeEnum::GOOD_FOOD]);
				} else {
					$this->production[CommodityTypeEnum::GOOD_FOOD]->lowerProduction($this->getBevFood());
				}
			} else {
				$obj = new ColonyProduction;
				$obj->setProduction(-$this->getBevFood());
				$obj->setGoodId(CommodityTypeEnum::GOOD_FOOD);
				$this->production[CommodityTypeEnum::GOOD_FOOD] = $obj;
			}
		}
		return $this->production;
	}

	private $productionsum = NULL;

	function getProductionSum() {
		if ($this->productionsum === NULL) {
			$sum = 0;
			foreach($this->getProduction() as $key => $value) {
				if ($value->getGood()->getType() == CommodityTypeEnum::GOOD_TYPE_EFFECT) {
					continue;
				}
				$sum += $value->getProduction();
			}
			$this->productionsum = $sum;
		}
		return $this->productionsum;
	}

	function getProductionSumDisplay() {
		if ($this->getProductionSum() <= 0) {
			return $this->getProductionSum();
		}
		return '+'.$this->getProductionSum();
	}

	function getProductionSumClass() {
		if ($this->getProductionSum() < 0) {
			return 'negative';
		}
		if ($this->getProductionSum() > 0) {
			return 'positive';
		}
		return '';
	}

	function getDayNightState() {
		//return 'day' for now
		return 't';
	}

	private $shiplist = NULL;	

	function getOrbitShipList(int $userId) {
		if ($this->shiplist === NULL) {
			$this->shiplist = array();
			$shiplist = Ship::getObjectsBy("WHERE systems_id=".$this->getSystemsId()." AND sx=".$this->getSX()." AND sy=".$this->getSY()." AND (user_id=".$userId." OR cloak=0) ORDER BY is_destroyed ASC, fleets_id DESC,id ASC");
			foreach ($shiplist as $key => $obj) {
				$this->shiplist[$obj->getFleetId()]['ships'][$obj->getId()] = $obj;
				if (!array_key_exists('name',$this->shiplist[$obj->getFleetId()])) {
					if ($obj->getFleetId() == 0) {
						$this->shiplist[$obj->getFleetId()]['name'] = _('Einzelschiffe');
					} else {
						$this->shiplist[$obj->getFleetId()]['name'] = $obj->getFleet()->getName();
					}
				}
			}
		}
		return $this->shiplist;
	}

	function getPlanetName() {
		return $this->data['planet_name'];
	}

	/**
	 */
	public function setPlanetName($value) { #{{{
		$this->setFieldValue('planet_name',$value,'getPlanetName');
	} # }}}

	function isFree() {
		return $this->getUserId() == USER_NOONE;
	}

	/**
	 * @return UserData
	 */
	public function getUser() {
		return ResourceCache()->getObject('user',$this->getUserId());
	}

	public function colonize(int $userId, BuildingInterface $building,$field=FALSE) {
		if (!$this->isFree()) {
			return;
		}
		$this->updateColonySurface();
		if (!$field) {
			$field = Colfields::getBy('colonies_id='.$this->getId().' AND type='.COLONY_FIELDTYPE_MEADOW.' ORDER BY RAND()');
		}
		$field->setBuildingId($building->getId());
		$field->setIntegrity($building->getIntegrity());
		$field->setActive(1);
		$field->save();
		$this->upperMaxBev($building->getHousing());
		$this->upperMaxEps($building->getEpsStorage());
		$this->upperMaxStorage($building->getStorage());
		$this->upperWorkers($building->getWorkers());
		$this->lowerWorkless($building->getWorkers());
		$this->upperWorkless($building->getHousing());
		$this->setUserId($userId);
		$this->upperEps($building->getEpsStorage());
		$this->setName(_('Kolonie'));
		$this->save();
		$this->upperStorage(CommodityTypeEnum::GOOD_BUILDING_MATERIALS,150);
		$this->upperStorage(CommodityTypeEnum::GOOD_FOOD,100);
	}

	function getBevFood() {
		return ceil(($this->getWorkers()+$this->getWorkless())/static::PEOPLE_FOOD);
	}

	function upperMaxBev($value) {
		$this->setMaxBev($this->getMaxBev()+$value);
	}

	function lowerMaxBev($value) {
		$this->setMaxBev($this->getMaxBev()-$value);
	}

	function setMaxBev($value) {
		$this->data['bev_max'] = $value;
		$this->addUpdateField('bev_max','getMaxBev');
	}

	function getMaxBev() {
		return $this->data['bev_max'];
	}

	function upperWorkers($value) {
		$this->setWorkers($this->getWorkers()+$value);
	}

	function lowerWorkers($value) {
		$this->setWorkers($this->getWorkers()-$value);
	}

	function setWorkers($value) {
		$this->data['bev_work'] = $value;;
		$this->addUpdateField('bev_work','getWorkers');
	}

	function getWorkers() {
		return $this->data['bev_work'];
	}

	function upperWorkless($value) {
		$this->setWorkless($this->getWorkless()+$value);
	}

	function lowerWorkless($value) {
		$this->setWorkless($this->getWorkless()-$value);
	}

	function setWorkless($value) {
		$this->data['bev_free'] = $value;;
		$this->addUpdateField('bev_free','getWorkless');
	}

	function getWorkless() {
		return $this->data['bev_free'];
	}

	function getPopulation() {
		return $this->getWorkers()+$this->getWorkless();
	}

	public function getFreeHousing() {
		return $this->getMaxBev()-$this->getPopulation();
	}

	public function hasOverpopulation() {
		return $this->getPopulation() > $this->getMaxBev();
	}

	function upperMaxEps($value) {
		$this->setMaxEps($this->getMaxEps()+$value);
	}

	function lowerMaxEps($value) {
		$this->setMaxEps($this->getMaxEps()-$value);
	}
	
	function setMaxEps($value) {
		$this->data['max_eps'] = $value;
		$this->addUpdateField('max_eps','getMaxEps');
	}

	function upperMaxStorage($value) {
		$this->setMaxStorage($this->getMaxStorage()+$value);
	}

	function lowerMaxStorage($value) {
		$this->setMaxStorage($this->getMaxStorage()-$value);
	}

	function setMaxStorage($value) {
		$this->data['max_storage'] = $value;
		$this->addUpdateField('max_storage','getMaxStorage');
	}

	function getSectorString() {
		$str = $this->getPosX().'|'.$this->getPosY();
		if ($this->isInSystem()) {
			$str .= ' ('.$this->getSystem()->getName().'-System)';
		}
		return $str;
	}

	function getImmigration() {
		if (!$this->getImmigrationState()) {
			return 0;
		}
		// TBD: depends on social things. return dummy for now
		$im = ceil(($this->getMaxBev()-$this->getPopulation)/4);
		if ($this->getPopulation()+$im > $this->getMaxBev()) {
			$im = $this->getMaxBev()-$this->getPopulation();
		}
		if ($this->getPopulationLimit() > 0 && $this->getPopulation()+$im > $this->getPopulationLimit()) {
			$im = $this->getPopulationLimit()-$this->getPopulation();
		}
		if ($im < 0) {
			return 0;
		}
		return round($im/100*$this->getPlanetType()->getBevGrowthRate());
	}

	function getBevGrowthSymbol() {
		if ($this->getImmigration() > 0) {
			return "+";
		}
		if ($this->getImmigration() == 0) {
			return "";
		}
		return "-";
	}

	function getGoodUseView() {
		// @todo refactor
		global $container;

		$commodityRepository = $container->get(CommodityRepositoryInterface::class);

		$stor = $this->getStorage();
		$prod = $this->getProduction();
		$ret = array();
		foreach ($prod as $key => $value) {
				$proc = $value->getProduction();
				if ($proc >= 0) {
					continue;
				}
				$ret[$key]['good'] = $commodityRepository->find((int) $value->getGoodId());
				$ret[$key]['production'] = $value;
				if (!array_key_exists($key,$stor)) {
					$ret[$key]['storage'] = 0;
				} else {
					$ret[$key]['storage'] = $stor[$key]->getAmount();
				}
				$ret[$key]['turnsleft'] = floor($ret[$key]['storage']/abs($proc));
		}
		return $ret;
	}

	function getPopulationLimit() {
		return $this->data['populationlimit'];
	}

	function setPopulationLimit($value) {
		$this->data['populationlimit'] = $value;
		$this->addUpdateField('populationlimit','getPopulationLimit');
	}

	function getImmigrationState() {
		return $this->data['immigrationstate'];
	}

	function setImmigrationState($value) {
		$this->data['immigrationstate'] = $value;
		$this->addUpdateField('immigrationstate','getImmigrationState');
	}

	public function updateColonySurface() {
		if (!$this->getMask()) {
			$generator = new PlanetGenerator();
			$surface = $generator->generateColony($this->getColonyClass(),$this->getSystem()->getBonusFieldAmount());
			$this->setMask(base64_encode(serialize($surface)));
			$this->save();
		}
		$surface = unserialize(base64_decode($this->getMask()));
		$fields = Colfields::getListBy('colonies_id='.$this->getId());
		$i = 0;
		foreach ($surface as $key => $value) {
			if (!array_key_exists($key,$fields)) {
				$fields[$key] = new ColfieldData;
				$fields[$key]->setColonyId($this->getId());
				$fields[$key]->setFieldId($i);
			}
			$fields[$key]->setFieldType($value);
			$fields[$key]->setBuildingId(0);
			$fields[$key]->setActive(0);
			$fields[$key]->setTerraformingId(0);
			$fields[$key]->save();
			$i++;
		}
		return $fields;
	}

	/**
	 */
	public function hasStorage() { #{{{
		return new ColonyStorageGoodWrapper($this->getStorage());
	} # }}}

	/**
	 */
	public function getNegativeEffect() { #{{{
		return ceil($this->getPopulation()/70);
	} # }}}

	private $positive_effect_primary = NULL;

	/**
	 */
	public function getPositiveEffectPrimary() { #{{{
		if ($this->positive_effect_primary === NULL) {
			$production = $this->getProduction();
			// XXX we should use a faction-factory...
			switch ($this->getUser()->getFaction()) {
				case FACTION_FEDERATION:
					$key = GOOD_SATISFACTION_FED_PRIMARY;
					break;
				case FACTION_ROMULAN:
					$key = GOOD_SATISFACTION_ROMULAN_PRIMARY;
					break;
				case FACTION_KLINGON:
					$key = GOOD_SATISFACTION_KLINGON_PRIMARY;
					break;
			}
			$this->positive_effect_primary = 0;
			if (!isset($production[$key])) {
				return 0;
			}
			$this->positive_effect_primary += $production[$key]->getProduction();
		}
		return $this->positive_effect_primary;
	} # }}}

	private $positive_effect_secondary = NULL;

	/**
	 */
	public function getPositiveEffectSecondary() { #{{{
		if ($this->positive_effect_secondary === NULL) {
			$production = $this->getProduction();
			$this->positive_effect_secondary = 0;
			// XXX we should use a faction-factory...
			switch ($this->getUser()->getFaction()) {
				case FACTION_FEDERATION:
					$key = GOOD_SATISFACTION_FED_SECONDARY;
					break;
				case FACTION_ROMULAN:
					$key = GOOD_SATISFACTION_ROMULAN_SECONDARY;
					break;
				case FACTION_KLINGON:
					$key = GOOD_SATISFACTION_KLINGON_SECONDARY;
					break;
			}
			if (!isset($production[$key])) {
				return 0;
			}
			$this->positive_effect_secondary += $production[$key]->getProduction();
		}
		return $this->positive_effect_secondary;
	} # }}}

	/**
	 */
	public function getPositiveEffectPrimaryDescription() { #{{{
		// XXX We need the other factions...
		switch ($this->getUser()->getFaction()) {
			case FACTION_FEDERATION:
				return _('Zufriedenheit');
			case FACTION_EMPIRE:
				return _('Loyalität');
		}
	} # }}}

	/**
	 */
	public function getPositiveEffectSecondaryDescription() { #{{{
		// XXX We need the other factions...
		switch ($this->getUser()->getFaction()) {
			case FACTION_FEDERATION:
				return _('Umweltkontrollen');
			case FACTION_EMPIRE:
				return _('Zerschmetterte Opposition');
		}
	} # }}}

	/**
	 */
	public function getNegativeEffectDescription() { #{{{
		// XXX We need the other factions...
		switch ($this->getUser()->getFaction()) {
			case FACTION_FEDERATION:
				return _('Umweltverschmutzung');
			case FACTION_EMPIRE:
				return _('Opposition');
		}
	} # }}}

	/**
	 */
	public function getCrewLimit() { #{{{
		return floor(
			min(
				max(
					$this->getPositiveEffectPrimary() - (4 * max(0, $this->getNegativeEffect() - $this->getPositiveEffectSecondary())),
					0
				),
				$this->getWorkers()
			)/5
		);
	} # }}}

	public function getProductionPreview() {
		return new ColonyProductionPreviewWrapper($this->getProduction());
	}

	/**
	 */
	public function getEpsProductionPreview() { #{{{
		return new ColonyEpsProductionPreviewWrapper($this);
		
	} # }}}

	/**
	 */
	public function getStorageSumPercent() { #{{{
		return round(100/$this->getMaxStorage()*$this->getStorageSum(),2);
	} # }}}

	/**
	 */
	public function clearCache() { #{{{
		$this->storage = NULL;
		$this->storagesum = NULL;
	} # }}}

	/**
	 */
	public function hasAirfield() { #{{{
		return count(Colfields::getFieldsByBuildingFunction($this->getId(),BUILDING_FUNCTION_AIRFIELD)) > 0;
	} # }}}

	/**
	 */
	public function hasModuleFab() { #{{{
		return count(Colfields::getFieldsByBuildingFunction($this->getId(),
				BuildingFunctionTypeEnum::getModuleFabOptions())) > 0;
	} # }}}

	/**
	 */
	public function hasShipyard() { #{{{
		return count(Colfields::getFieldsByBuildingFunction($this->getId(),
				BuildingFunctionTypeEnum::getShipyardOptions())) > 0;
	} # }}}
	
	private $has_active_building_by_function = array();

	/**
	 */
	public function hasActiveBuildingWithFunction($function_id) { #{{{
		if (!isset($this->has_active_building_by_function[$function_id])) {
			$this->has_active_building_by_function[$function_id] = count(Colfields::getFieldsByBuildingFunction($this->getId(),$function_id,TRUE)) > 0;
		}
		return $this->has_active_building_by_function[$function_id];
	} # }}}

	/**
	 */
	public function getEpsBoxTitleString() { #{{{
		return sprintf(_('Energie: %d/%d (%d/Runde = %d)'),$this->getEps(),$this->getMaxEps(),$this->getEpsProductionDisplay(),$this->getEpsProductionForecast());
	} # }}}


}
class Colony extends ColonyData {
	
	function __construct($colony_id) {
		$result = DB()->query("SELECT * FROM stu_colonies WHERE id=".intval($colony_id),4);
		if ($result == 0) {
			throw new ObjectNotFoundException($colony_id);
		}
		parent::__construct($result);
	}

	static function getListBy($where='') {
		$result = DB()->query("SELECT * FROM stu_colonies WHERE ".$where);
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new ColonyData($data);
		}
		return $ret;
	}

	static function getBy($where='') {
		$result = DB()->query("SELECT * FROM stu_colonies WHERE ".$where,4);
		if ($result == 0) {
			return FALSE;
		}
		return new ColonyData($result);
	}

	/**
	 */
	static function getFreeColonyList($faction_id) { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' WHERE user_id='.USER_NOONE.' AND colonies_classes_id IN (SELECT id FROM stu_colonies_classes WHERE allow_start=1) AND systems_id IN (select systems_id FROM stu_map WHERE systems_id>0 AND region_id IN (SELECT region_id from stu_map_regions_settlement WHERE faction_id='.$faction_id.'))');
		return self::_getList($result,'ColonyData');
	} # }}}

	/**
	 */
	static function countInstances($sql) { #{{{
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." ".$sql,1);
	} # }}}

} #}}}
