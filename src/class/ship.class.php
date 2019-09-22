<?php


use Stu\Lib\DamageWrapper;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Starmap\View\Overview\Overview;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\ShipStorageInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\ShipStorageRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class ShipData extends BaseTable {

	const tablename = 'stu_ships';
	protected $tablename = 'stu_ships';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	function getId() {
		return (int) $this->data['id'];
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getRumpId() {
		return $this->data['rumps_id'];
	}

	function setName($value) {
		$this->setFieldValue('name',$value,'getName');
	}

	function getName() {
		return $this->data['name'];
	}

	function getCX() {
		return $this->data['cx'];
	}
	
	function getCY() {
		return $this->data['cy'];
	}

	function getSX() {
		return $this->data['sx'];
	}
	
	function getSY() {
		return $this->data['sy'];
	}

	function getPosX() {
		if ($this->isInSystem()) {
			return $this->getSX();
		}
		return $this->getCX();
	}
	
	function getPosY() {
		if ($this->isInSystem()) {
			return $this->getSY();
		}
		return $this->getCY();
	}

	function isInSystem() {
		return $this->getSystemsId()>0;
	}

	function getHuell() {
		return $this->data['huelle'];
	}

	/**
	 */
	public function setMaxHuelle($value) { # {{{
		$this->setFieldValue('max_huelle',$value,'getMaxHuell');
	} # }}}

	/**
	 */
	public function getMaxHuell() { # {{{
		return $this->data['max_huelle'];
	} # }}}

	function getShield() {
		return $this->data['schilde'];
	}

	/**
	 */
	public function setMaxShield($value) { # {{{
		$this->setFieldValue('max_schilde',$value,'getMaxShield');
	} # }}}

	/**
	 */
	public function getMaxShield() { # {{{
		return $this->data['max_schilde'];
	} # }}}

	
	function getEps() {
		return $this->data['eps'];
	}

	/**
	 */
	public function setMaxEps($value) { # {{{
		$this->setFieldValue('max_eps',$value,'getMaxEps');
	} # }}}

	/**
	 */
	public function getMaxEps() { # {{{
		return $this->data['max_eps'];
	} # }}}

	function getEBatt() {
		return $this->data['batt'];
	}

	function setEBatt($value) {
		$this->data['batt'] = $value;
		$this->addUpdateField('batt','getEBatt');
	}

	/**
	 */
	public function setMaxEbatt($value) { # {{{
		$this->setFieldValue('max_batt',$value,'getMaxEbatt');
	} # }}}

	/**
	 */
	public function getMaxEbatt() { # {{{
		return $this->data['max_batt'];
	} # }}}

	private $crew = NULL;

	public function getCrewlist() {
		if ($this->crew === NULL) {
			// @todo refactor
			global $container;

			$this->crew = $container->get(ShipCrewRepositoryInterface::class)->getByShip((int) $this->getId());
		}
		return $this->crew;
	}

	/**
	 */
	public function getCrewCount() { #{{{
	    // @todo refactor
		global $container;

		return $container->get(ShipCrewRepositoryInterface::class)->getAmountByShip((int) $this->getId());
	} # }}}

	function getAlertState() {
		return $this->data['alvl'];
	}

	function setAlertState($value) {
		$this->data['alvl'] = $value;
		$this->addUpdateField('alvl','getAlertState');
	}
	
	function phaserIsActive() {
		return $this->data['wea_phaser'] == 1;
	}

	function torpedoIsActive() {
		return $this->data['wea_torp'] == 1;
	}

	function isInFleet() {
		return $this->getFleetId() > 0;
	}

	function getFleetId() {
		return $this->data['fleets_id'];
	}

	public function leaveFleet() {
		$this->setFleetId(0);
		$this->save();
	}

	function setFleetId($value) {
		$this->data['fleets_id'] = $value;
		$this->addUpdateField('fleets_id','getFleetId');
	}

	function ownedByCurrentUser() {
		return $this->getUserId() == currentUser()->getId();
	}

	private $fleet = NULL;

	function getFleet() {
		// @todo refactor
		global $container;

		return $container->get(FleetRepositoryInterface::class)->find((int) $this->getFleetId());
	}

	function isFleetLeader() {
		if (!$this->isInFleet()) {
			return FALSE;
		}
		return $this->getFleet()->getFleetLeader() == $this->getId();
	}

	function isBase() {
		return $this->data['is_base'] > 0;
	}

	function getSlots() {
		return $this->data['slots'];
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function getUser(): UserInterface {
		// @todo refactor
		global $container;

		return $container->get(UserRepositoryInterface::class)->find($this->getUserId());
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}
	
	function setCX($value,$force=FALSE) {
		$this->data['cx'] = $value;
		if ($force) {
			$this->addUpdateField('cx','getCX');
			return;
		}
		$this->addUpdateField('cx','getPosX');
	}
	
	function setSX($value) {
		$this->data['sx'] = $value;
		$this->addUpdateField('sx','getPosX');
	}
	
	function setCY($value,$force=FALSE) {
		$this->data['cy'] = $value;
		if ($force) {
			$this->addUpdateField('cy','getCY');
			return;
		}
		$this->addUpdateField('cy','getPosY');
	}
	
	function setSY($value) {
		$this->data['sy'] = $value;
		$this->addUpdateField('sy','getPosY');
	}

	function setPosX($value) {
		if ($this->isInSystem()) {
			$this->setSX($value);
			return;
		}
		$this->setCX($value);
	}
	
	function setPosY($value) {
		if ($this->isInSystem()) {
			$this->setSY($value);
			return;
		}
		$this->setCY($value);
	}

	private $system = NULL;

	function getSystem() {
		if ($this->system === NULL) {
			// @todo refactor
			global $container;

			$this->system = $container->get(StarSystemRepositoryInterface::class)->find((int) $this->getSystemsId());
		}
		return $this->system;
	}

	function getSystemsId() {
		return $this->data['systems_id'];
	}

	function setSystemsId($value) {
		$this->data['systems_id'] = $value;
		$this->addUpdateField('systems_id','getSystemsId');
	}

	public function getWarpcoreLoad() {
		return $this->data['warpcore'];
	}

	public function setWarpcoreLoad($value) {
		$this->setFieldValue('warpcore',$value,'getWarpcoreLoad');
	}

	public function getWarpcoreCapacity() {
		return $this->getReactorOutput()*WARPCORE_CAPACITY_MULTIPLIER;
	}

	public function getReactorCapacity() {
		if ($this->getReactorOutput() > $this->getWarpcoreLoad()) {
			return $this->getWarpcoreLoad();
		}
		return $this->getReactorOutput();
	}
	
	private $effectiveEpsProduction = NULL;

	public function getEffectiveEpsProduction() {
		if ($this->effectiveEpsProduction === NULL) {
			$prod = $this->getReactorCapacity()-$this->getEpsUsage();
			if ($prod <= 0) {
				return $prod;
			}
			if ($this->getEps() + $prod > $this->getMaxEps()) {
				return $this->getMaxEps()-$this->getEps();
			}
			$this->effectiveEpsProduction = $prod;
		}
		return $this->effectiveEpsProduction;
	}

	public function getWarpcoreUsage() {
		return $this->getEffectiveEpsProduction()+$this->getEpsUsage();
	}

	function getWarpState() {
		return $this->data['warp'];
	}

	function setWarpState($value) {
		$this->data['warp'] = $value;
		$this->addUpdateField('warp','getWarpState');
	}

	function isEBattUseable() {
		return $this->getEBattWaitingTime() < time();
	}

	function setEBattWaitingTime($value) {
		$this->data['batt_wait'] = time()+$value;
		$this->addUpdateField('batt_wait','getEBattWaitingTime');
	}

	function getEBattWaitingTime() {
		return $this->data['batt_wait'];
	}

	function isCloakAble() {
		return $this->data['cloakable'];
	}

	/**
	 */
	public function setCloakable($value) { # {{{
		$this->setFieldValue('cloakable',$value,'isCloakable');
	} # }}}

	function isWarpAble() {
		// @todo TBD damaged warp coils
		return TRUE;
	}

	function getCloakState() {
		return $this->data['cloak'];
	}

	function getTraktorMode() {
		return $this->data['traktormode'];
	}

	function isTraktorbeamActive() {
		return $this->getTraktorMode() > 0;
	}

	function traktorBeamFromShip() {
		return $this->getTraktorMode() == 1;
	}

	function traktorBeamToShip() {
		return $this->getTraktorMode() == 2;
	}
	
	private $traktorship = NULL;

	function getTraktorShip(): ShipData {
		if ($this->traktorship === NULL) {
			$this->traktorship = Ship::getById($this->getTraktorShipId());
		}
		return $this->traktorship;
	}

	function unsetTraktor() {
		$this->setTraktorMode(0);
		$this->setTraktorShipId(0);
		$this->save();
	}

	function getTraktorShipId() {
		return $this->data['traktor'];
	}

	function setTraktorShipId($value) {
		$this->data['traktor'] = $value;
		$this->addUpdateField('traktor','getTraktorShipId');
	}

	function setTraktorMode($value) {
		$this->data['traktormode'] = $value;
		$this->addUpdateField('traktormode','getTraktorMode');
	}

	/**
	 */
	public function deactivateTraktorBeam() { #{{{
		if (!$this->getTraktorMode()) {
			return;
		}
		$ship = ResourceCache()->getObject('ship',$this->getTraktorShipId());
		$this->setTraktorMode(0);
		$this->setTraktorShipId(0);
		$ship->setTraktorMode(0);
		$ship->setTraktorShipId(0);
		$this->save();
		$ship->save();
	} # }}}

	/**
	 */
	public function setIsDestroyed($value) { # {{{
		$this->setFieldValue('is_destroyed',$value,'getIsDestroyed');
	} # }}}

	/**
	 */
	public function getIsDestroyed() { # {{{
		return $this->data['is_destroyed'];
	} # }}}
	
	function isOverSystem() {
		if ($this->isInSystem()) {
			return FALSE;
		}
		if ($this->system === NULL) {
			// @todo refactor
			global $container;

			$this->system = $container->get(StarSystemRepositoryInterface::class)->getByCoordinates(
				(int) $this->getCX(),
				(int) $this->getCY()
			);
		}
		return $this->system;
	}

	function isWarpPossible() {
		return $this->hasShipSystem(SYSTEM_WARPDRIVE) && !$this->isInSystem();
	}

	function setEps($eps) {
		$this->data['eps'] = $eps;
		$this->addUpdateField('eps','getEps');
	}

	function setCloak($value) {
		$this->data['cloak'] = $value;
		$this->addUpdateField('cloak','getCloakState');
	}

	function setShieldState($value) {
		$this->data['schilde_status'] = $value;
		$this->addUpdateField('schilde_status','shieldIsActive');
	}

	function setShield($value) {
		$this->data['schilde'] = $value;
		$this->addUpdateField('schilde','getShield');
	}

	function setHuell($value) {
		$this->data['huelle'] = $value;
		$this->addUpdateField('huelle','getHuell');
	}

	function lowerHuell($value) {
		$this->setHuell($this->getHuell()-$value);
	}

	function setPhaser($value) {
		$this->data['wea_phaser'] = $value;
		$this->addUpdateField('wea_phaser','phaserIsActive');
	}

	function setTorpedos($value) {
		$this->data['wea_torp'] = $value;
		$this->addUpdateField('wea_torp','torpedoIsActive');
	}

	public function hasActiveWeapons() {
		return $this->phaserIsActive() || $this->torpedoIsActive();
	}
	
	function getLss() {
		return $this->data['lss'];
	}

	function setLss($value) {
		$this->data['lss'] = $value;
		$this->addUpdateField('lss','getLss');
	}

	function getNbs() {
		return $this->data['nbs'];
	}

	function lowerEps($value) {
		$this->setEps($this->getEps()-$value);
	}

	function upperEps($value) {
		$this->setEps($this->getEps()+$value);
	}

	function setNbs($value) {
		$this->data['nbs'] = $value;
		$this->addUpdateField('nbs','getNbs');
	}

	function shieldIsActive() {
		return $this->data['schilde_status'];
	}

	function getShieldState() {
		return $this->data['schilde_status'];
	}

	function cloakIsActive() {
		return $this->getCloakState() == 1;
	}

	/**
	 */
	public function setTorpedoCount($value) { # {{{
		$this->setFieldValue('torpedo_count',$value,'getTorpedoCount');
	} # }}}

	/**
	 */
	public function getTorpedoCount() { # {{{
		return $this->data['torpedo_count'];
	} # }}}

	/**
	 */
	public function setTorpedoType($value) { # {{{
		$this->setFieldValue('torpedo_type',$value,'getTorpedoType');
	} # }}}

	/**
	 */
	public function getTorpedoType() { # {{{
		return $this->data['torpedo_type'];
	} # }}}

	private $torpedo = NULL;

	/**
	 */
	public function getTorpedo() { #{{{
		if ($this->torpedo === NULL) {
			// @todo refactor
            global $container;

            $this->torpedo = $container->get(TorpedoTypeRepositoryInterface::class)->find((int) $this->getTorpedoType());
		}
		return $this->torpedo;
	} # }}}

	/**
	 */
	public function setFormerRumpsId($value) { # {{{
		$this->setFieldValue('former_rumps_id',$value,'getFormerRumpsId');
	} # }}}

	/**
	 */
	public function getFormerRumpsId() { # {{{
		return $this->data['former_rumps_id'];
	} # }}}

	/**
	 */
	public function damage(DamageWrapper $damage_wrapper) { #{{{
		$this->setShieldRegenerationTimer(time());
		$msg = array();
		if ($this->shieldIsActive()) {
			$damage = $damage_wrapper->getDamageRelative($this,DAMAGE_MODE_SHIELDS);
			if ($damage > $this->getShield()) {
				$msg[] = "- Schildschaden: ".$this->getShield();
				$msg[] = "-- Schilde brechen zusammen!";
				$this->setShieldState(0);
				$this->setShield(0);
			} else {
				$this->setShield($this->getShield()-$damage);
				$msg[] = "- Schildschaden: ".$damage." - Status: ".$this->getShield();
			}
		}
		if ($damage_wrapper->getDamage() <= 0) {
			return $msg;
		}
		$disablemessage = FALSE;
		$damage = $damage_wrapper->getDamageRelative($this,DAMAGE_MODE_HULL);
		if ($this->getCanBeDisabled() && $this->getHuell()-$damage < round($this->getMaxHuell()/100*10)) {
			$damage = round($this->getHuell()-$this->getMaxHuell()/100*10);
			$disablemessage = _('-- Das Schiff wurde kampfunfähig gemacht');
			$this->setDisabled(1);
		}
		if ($this->getHuell() > $damage) {
			$this->setHuell($this->getHuell()-$damage);
			$msg[] = "- Hüllenschaden: ".$damage." - Status: ".$this->getHuell();
			if ($disablemessage) {
				$msg[] = $disablemessage;
			}
			return $msg;
		}
		$msg[] = "- Hüllenschaden: ".$damage;
		$msg[] = "-- Das Schiff wurde zerstört!";
		$this->setIsDestroyed(1);
		return $msg;
	} # }}}

	function setRumpId($value) {
		$this->data['rumps_id'] = $value;
		$this->addUpdateField('rumps_id','getRumpId');
	}

	/**
	 */
	public function deactivateSystems() { #{{{
		$this->deactivateTraktorBeam();
		$this->setShieldState(0);
		$this->setNbs(0);
		$this->setLss(0);
		$this->setPhaser(0);
		$this->setTorpedos(0);
	} # }}}

	public function clearCache(): void
	{
		$this->rump = null;
		$this->storage = null;
	}

	function getFlightDirection() {
		return $this->data['direction'];
	}

	function setFlightDirection($value) {
		$this->data['direction'] = $value;
		$this->addUpdateField('direction','getFlightDirection');
	}

	private $storage = NULL;

	/**
	 * @return ShipStorageInterface[] Indexed by commodityId
	 */
	public function getStorage() {
		if ($this->storage === NULL) {
			// @todo refactor
			global $container;

			$this->storage = $container->get(ShipStorageRepositoryInterface::class)->getByShip((int) $this->getId());
		}
		return $this->storage;
	}

	function getStorageSum() {
	    return array_reduce(
	    	$this->getStorage(),
		    function (int $sum, ShipStorageInterface $storage): int {
	    		return $sum + $storage->getAmount();
		    },
		    0
	    );
	}

	function getMaxStorage() {
		return $this->getRump()->getStorage();
	}

	private $currentColony = NULL;

	function getCurrentColony() {
		if ($this->currentColony === NULL) {
			// @todo refactor
			global $container;

			$colonyRepository = $container->get(ColonyRepositoryInterface::class);

			$this->currentColony = $colonyRepository->getByPosition(
				(int)$this->getSystemsId(),
				(int)$this->getPosX(),
				(int)$this->getPosY()
			);
		}
		return $this->currentColony;
	}

	function getSectorString() {
		$str = $this->getPosX().'|'.$this->getPosY();
		if ($this->isInSystem()) {
			$str .= ' ('.$this->getSystem()->getName().'-System)';
		}
		return $str;
	}

	function getDatabaseId() {
		return $this->data['database_id'];
	}

	function setDatabaseId($value) {
		$this->data['database_id'] = $value;
		$this->addUpdateField('database_id','getDatabaseId');
	}

	private $rump = NULL;

	function getRump(): ShipRumpInterface
	{
		if ($this->rump === NULL) {
			// @todo refactor
			global $container;

			$this->rump = $container->get(ShipRumpRepositoryInterface::class)->find((int) $this->getRumpId());
		}
		return $this->rump;
	}

	public function hasPhaser() {
		return $this->hasShipSystem(SYSTEM_PHASER);
	}

	public function hasTorpedo() {
		return $this->hasShipSystem(SYSTEM_TORPEDO);
	}

	public function hasWarpcore() {
		return $this->hasShipSystem(SYSTEM_WARPCORE);
	}

	public function getMaxTorpedos() {
		return $this->getRump()->getBaseTorpedoStorage();
	}

	private $buildplan = NULL;

	public function getBuildplanId() {
		return $this->data['plans_id'];
	}

	public function setBuildplanId($value) {
		$this->setFieldValue('plans_id',$value,'getBuildplanId');
	}

	public function getBuildplan(): ShipBuildplanInterface {
		if ($this->buildplan === NULL) {
			// @todo refactor
			global $container;

			$this->buildplan = $container->get(ShipBuildplanRepositoryInterface::class)->find(
				(int) $this->getBuildplanId()
			);
		}
		return $this->buildplan;
	}

	private $activeSystems = NULL;
	private $epsUsage = NULL;

	public function getEpsUsage() {
		if ($this->epsUsage === NULL) {
			$this->epsUsage = 0;
			foreach ($this->getActiveSystems() as $key => $obj) {
				$this->epsUsage += $obj->getEnergyCosts();
			}
		}
		return $this->epsUsage;
	}

	public function lowerEpsUsage($value) {
		$this->epsUsage -= $value;
	}
	
	private $systems = NULL;

	public function getSystems() {
		if ($this->systems === NULL) {
			// @todo refactor
			global $container;

			$this->systems = [];
			foreach ($container->get(ShipSystemRepositoryInterface::class)->getByShip((int) $this->getId()) as $system) {
				$this->systems[$system->getSystemType()] = $system;
			}
		}
		return $this->systems;
	}

	public function hasShipSystem($system) {
		return array_key_exists($system,$this->getSystems());
	}

	public function getShipSystem($system): ShipSystemInterface {
		$arr = &$this->getSystems();
		return $arr[$system];
	}

	/**
	 * @return ShipSystemInterface[]
	 */
	public function getActiveSystems() {
		if ($this->activeSystems !== NULL) {
			return $this->activeSystems;
		}
		$ret = array();
		foreach ($this->getSystems() as $key => $obj) {
			if (!$this->isActiveSystem($obj)) {
				continue;
			}
			$ret[$key] = $obj;
		}
		return $this->activeSystems = $ret;
	}

	public function isActiveSystem($system) {
		return $this->data[$this->getShipField($system)] >= 1;
	}

	private function getShipField(ShipSystemInterface $shipSystem): string {
		switch ($shipSystem->getSystemType()) {
			case SYSTEM_CLOAK:
				return 'cloak';
			case SYSTEM_NBS:
				return 'nbs';
			case SYSTEM_LSS:
				return 'lss';
			case SYSTEM_PHASER:
				return 'wea_phaser';
			case SYSTEM_TORPEDO:
				return 'wea_torp';
			case SYSTEM_WARPDRIVE:
				return 'warp';
			case SYSTEM_SHIELDS:
				return 'schilde_status';
		}
		return '';
	}

	public function systemIsActivateable($system) {
		if (!$this->hasShipSystem($system)) {
			return FALSE;
		}
		if (!$this->getShipSystem($system)->isActivateable()) {
			return FALSE;
		}
		if ($this->getShipSystem($system)->getEnergyCosts() > $this->getEps()) {
			return FALSE;
		}
		if (array_key_exists($system,$this->getActiveSystems())) {
			return FALSE;
		}
		switch ($system) {
			case SYSTEM_SHIELDS:
				if ($this->getShield() == 0) {
					return FALSE;
				}
		}
		return TRUE;
	}

	public function activateSystem($system,$use_eps=TRUE) {
		if (!$this->hasShipSystem($system)) {
			return;
		}
		$cb = $this->getShipSystem($system)->getShipCallback();
		$this->$cb(1);
		if ($use_eps) {
			$this->lowerEps($this->getShipSystem($system)->getEnergyCosts());
		}
	}

	public function deactivateSystem($system) {
		if (!$this->hasShipSystem($system)) {
			return;
		}
		$cb = $this->getShipSystem($system)->getShipCallback();
		$this->$cb(0);
	}

	public function getBase() {
		return $this->data['is_base'];
	}

	public function setBase($value) {
		$this->setFieldValue('is_base',$value,'getBase');
	}

	public function displayNbsActions() {
		return $this->getCloakState() == 0 && $this->getWarpstate() == 0;
	}

	public function traktorbeamNotPossible() {
		return  $this->getBase() || $this->getRump()->isTrumfield() || $this->getCloakState() || $this->getShieldState() || $this->getWarpState();
	}

	public function isInterceptAble() {
		return $this->getUserId() != currentUser()->getId() && $this->getWarpState();
	}

	public function getMapCX() {
		return ceil($this->getCX() / Overview::FIELDS_PER_SECTION);
	}

	public function getMapCY() {
		return ceil($this->getCY() / Overview::FIELDS_PER_SECTION);
	}

	public function getCrewBySlot($slot): array {
	    // @todo refactor
		global $container;

		return $container->get(ShipCrewRepositoryInterface::class)->getByShipAndSlot(
			(int) $this->getId(),
			(int) $slot
		);
	}

	public function getTradePostId() {
		return $this->data['trade_post_id'];
	}

	public function setTradePostId($value) {
		$this->setFieldValue('trade_post_id',$value,'getTradePostId');
	}

	public function getDock() {
		return $this->data['dock'];
	}

	public function setDock($value) {
		$this->setFieldValue('dock',$value,'getDock');
	}

	public function getDockedShip(): ShipData {
		return ResourceCache()->getObject('ship',$this->getDock());
	}

	public function dockedOnTradePost() {
		return $this->getDock() && $this->getDockedShip()->getTradePostId() > 0;
	}

	private $dockPrivileges = NULL;

	public function getDockPrivileges() {
		if ($this->dockPrivileges === NULL) {
			// @todo refactor
			global $container;

			$this->dockPrivileges = $container->get(DockingPrivilegeRepositoryInterface::class)->getByShip(
				(int) $this->getId()
			);
		}
		return $this->dockPrivileges;
	}

	public function hasFreeDockingSlots() {
		return $this->getRump()->getDockingSlots() > $this->getDockedShipCount();
	}

	public function getFreeDockingSlotCount() {
		return $this->getRump()->getDockingSlots()-$this->getDockedShipCount();
	}

	public function getDockedShipCount() {
		return Ship::countInstances('WHERE dock='.$this->getId());
	}

	private $mapfield = NULL;

	public function getCurrentMapField() {
		if ($this->mapfield === NULL) {
			// @todo refactor
			global $container;
			if (!$this->isInSystem()) {
			    $this->mapfield = $container->get(MapRepositoryInterface::class)->getByCoordinates(
				    (int) $this->getCX(),
				    (int) $this->getCY()
			    );
			} else {
				// @todo refactor
				global $container;

				$this->mapfield = $container->get(StarSystemMapRepositoryInterface::class)->getByCoordinates(
					(int) $this->getSystemsId(),
					(int) $this->getSX(),
					(int) $this->getSY()
				);
			}
		}
		return $this->mapfield;
	}

	public function getDisabled() {
		return $this->data['disabled'];
	}

	public function setDisabled($value) {
		$this->setFieldValue('disabled',$value,'getDisabled');
	}

	/**
	 */
	public function setCanBeDisabled($value) { # {{{
		$this->setFieldValue('can_be_disabled',$value,'getCanBeDisabled');
	} # }}}

	/**
	 */
	public function getCanBeDisabled() { # {{{
		return $this->data['can_be_disabled'];
	} # }}}

	/**
	 */
	public function setHitChance($value) { # {{{
		$this->setFieldValue('hit_chance',$value,'getHitChance');
	} # }}}

	/**
	 */
	public function getHitChance() { # {{{
		return $this->data['hit_chance'];
	} # }}}

	/**
	 */
	public function setEvadeChance($value) { # {{{
		$this->setFieldValue('evade_chance',$value,'getEvadeChance');
	} # }}}

	/**
	 */
	public function getEvadeChance() { # {{{
		return $this->data['evade_chance'];
	} # }}}
	
	/**
	 */
	public function setReactorOutput($value) { # {{{
		$this->setFieldValue('reactor_output',$value,'getReactorOutput');
	} # }}}

	/**
	 */
	public function getReactorOutput() { # {{{
		return $this->data['reactor_output'];
	} # }}}

	/**
	 */
	public function setBaseDamage($value) { # {{{
		$this->setFieldValue('base_damage',$value,'getBaseDamage');
	} # }}}

	/**
	 */
	public function getBaseDamage() { # {{{
		return $this->data['base_damage'];
	} # }}}

	/**
	 */
	public function setSensorRange($value) { # {{{
		$this->setFieldValue('sensor_range',$value,'getSensorRange');
	} # }}}

	/**
	 */
	public function getSensorRange() { # {{{
		return $this->data['sensor_range'];
	} # }}}

	/**
	 */
	public function setShieldRegenerationTimer($value) { # {{{
		$this->setFieldValue('shield_regeneration_timer',$value,'getShieldRegenerationTimer');
	} # }}}

	/**
	 */
	public function getShieldRegenerationTimer() { # {{{
		return $this->data['shield_regeneration_timer'];
	} # }}}

	/**
	 */
	private function getShieldRegenerationPercentage() { #{{{
		// XXX
		return 10;
	} # }}}

	/**
	 */
	public function getShieldRegenerationRate() { #{{{
		return ceil(($this->getMaxShield()/100)*$this->getShieldRegenerationPercentage());
	} # }}}

	/**
	 */
	public function canIntercept() { #{{{
		return !$this->getTraktorMode();
	} # }}}

	/**
	 */
	public function canLandOnCurrentColony() { #{{{
		if (!$this->getRump()->getGoodId()) {
			return FALSE;
		}
		if (!$this->getCurrentColony()) {
			return FALSE;
		}
		if (!$this->getCurrentColony()->ownedByCurrentUser()) {
			return FALSE;
		}

		// @todo refactor
		global $container;
		return $container->get(ColonyLibFactoryInterface::class)
			->createColonySurface($this->getCurrentColony())
			->hasAirfield();
	} # }}}

	/**
	 */
	public function canBeAttacked() { #{{{
		return !$this->ownedByCurrentUser() && !$this->getRump()->isTrumfield();
	} # }}}

	public function canAttack(): bool {
		return $this->phaserIsActive() || $this->torpedoIsActive();
	}

	/**
	 */
	public function hasEscapePods() { #{{{
		return $this->getRump()->isTrumfield() && $this->getCrewCount() > 0;
	} # }}}

	/**
	 */
	public function canBeRepaired() { #{{{
		// TODO
		if ($this->getHuell() >= $this->getMaxHuell()) {
			return FALSE;
		}
		if ($this->shieldIsActive()) {
			return FALSE;
		}
		return TRUE;
	} # }}}

	/**
	 */
	public function setState($value) { # {{{
		$this->setFieldValue('state',$value,'getState');
	} # }}}

	/**
	 */
	public function getState() { # {{{
		return $this->data['state'];
	} # }}}

	/**
	 */
	public function cancelRepair() { #{{{
		if ($this->getState() == SHIP_STATE_REPAIR) {
			$this->setState(SHIP_STATE_NONE);

			// @todo inject
			global $container;
			$container->get(ColonyShipRepairRepositoryInterface::class)->truncateByShipId($this->getId());

			$this->save();
		}
	} # }}}

	/**
	 */
	public function getRepairRate() { #{{{
		// TODO
		return 100;
	} # }}}

	public function canInteractWith($target, bool $colony = false): bool
	{
		if (!checkPosition($this, $target) || $this->getCloakState() || ($colony && $target->getId() == $this->getId())) {
			new ObjectNotFoundException($target->getId());
		}
		if ($colony) {
			return true;
		}
		if ($target->shieldIsActive() && $target->getUserId() != $this->getUserId()) {
			return false;
		}
		return true;

	}
}
class Ship extends ShipData {

	function __construct($ship_id) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$ship_id." LIMIT 1",4);
		if ($result == 0) {
			throw new ObjectNotFoundException($ship_id);
		}
		parent::__construct($result);
	}

	static public function getById($shipId) {
		return ResourceCache()->getObject("ship",$shipId);
	}

	/**
	 * @return ShipData[]
	 */
	static function getObjectsBy($qry="") {
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$qry);
		return self::_getList($result,'ShipData','id','ship');
	}

	static function getObjectBy($qry="") {
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$qry." LIMIT 1",4);
		if (!$result) {
			return FALSE;
		}
		$data = new ShipData($result);
		ResourceCache()->registerResource('ship',$data->getId(),$data);
		return $data;
	}

	static public function countInstances($qry="") {
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." ".$qry,1);
	}

	/**
	 * @return ShipData[]
	 */
	public static function getShipsBy(&$fleetId, $without = [0])
	{
		$ret = [];
		$result = DB()->query("SELECT * FROM stu_ships WHERE fleets_id=" . $fleetId . " AND id NOT IN (" . join(",",
				$without) . ") ORDER BY id DESC,is_base DESC, id LIMIT 200");
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[] = new ShipData($data);
		}
		return $ret;
	}
}

?>
