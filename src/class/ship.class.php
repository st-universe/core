<?php


use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;

class ShipData extends BaseTable {

	const tablename = 'stu_ships';
	protected $tablename = 'stu_ships';

	private $sessionString = NULL;
	
	function __construct(&$data=array()) {
		$this->data = $data;
		$this->getCrewList();
	}

	function getId() {
		return $this->data['id'];
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getRumpId() {
		return $this->data['rumps_id'];
	}

	function setName($value) {
		if ($value == $this->getName()) {
			return; 
		}
		$old = $this->getName();
		$value = strip_tags($value);
		$this->data['name'] = $value;
		if (strlen($this->getNameWithoutMarkup()) < 3) {
			$this->data['name'] = $old;
			return;
		}
		$this->setFieldValue('name',$value,'getName');
	}

	function getName() {
		return $this->data['name'];
	}

	function getNameWithoutMarkup() {
		return strip_tags(BBCode()->parse($this->getName()));
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
			$this->crew = ShipCrew::getByShip($this->getId());
		}
		return $this->crew;
	}

	/**
	 */
	public function getCrewCount() { #{{{
		return ShipCrew::countInstances('WHERE ships_id='.$this->getId());
	} # }}}

	public function getCrew() {
		return count($this->getCrewlist());
	}

	function nbsIsActive() {
		return $this->getNbs() == 1;
	}
	
	function lssIsActive() {
		return $this->getLss() == 1;
	}

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
		$this->unsetFleet();
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
		if ($this->fleet === NULL && $this->isInFleet()) {
			$this->fleet = ResourceCache()->getObject(CACHE_FLEET,$this->getFleetId());
		}
		return $this->fleet;
	}

	function isFleetLeader() {
		if (!$this->isInFleet()) {
			return FALSE;
		}
		return $this->getFleet()->getFleetLeader() == $this->getId();
	}

	function setFleet(&$obj) {
		$this->fleet = $obj;
	}

	function unsetFleet() {
		$this->fleet = NULL;
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

	function getUser() {
		return ResourceCache()->getObject("user",$this->getUserId());
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
			$this->system = new StarSystem($this->getSystemsId());
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

        /**
         */
        public function upperWarpcoreLoad($value) { #{{{
                $this->setWarpcoreLoad($this->getWarpcoreLoad()+$value);
        } # }}}

	public function lowerWarpcoreLoad($value) {
		$this->setWarpcoreLoad($this->getWarpcoreLoad()-$value);
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
	
	public function getEpsProduction() {
		return $this->getReactorCapacity();
	}

	private $effectiveEpsProduction = NULL;

	public function getEffectiveEpsProduction() {
		if ($this->effectiveEpsProduction === NULL) {
			$prod = $this->getEpsProduction()-$this->getEpsUsage();
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

	public function getEffectiveEpsProductionClass() {
		if ($this->getEffectiveEpsProduction() > 0) {
			return 'pos';
		}
		if ($this->getEffectiveEpsProduction() < 0) {
			return 'neg';
		}
		return '';
	}

	public function getEffectiveEpsProductionDisplay() {
		if ($this->getEffectiveEpsProduction() > 0) {
			return '+';
		}
		return '';
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

	function hasEmergencyBattery() {
		return $this->getMaxEbatt() > 0;
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

	function getEBattWaitingTimeFormatted() {
		return date("d.m.Y H:i",$this->getEBattWaitingTime());
	}

	function isCloakAble() {
		return $this->data['cloakable'];
	}

	/**
	 */
	public function setCloakable($value) { # {{{
		$this->setFieldValue('cloakable',$value,'getCloakable');
	} # }}}

	/**
	 */
	public function getCloakable() { # {{{
		return $this->data['cloakable'];
	} # }}}

	
	function isWarpAble() {
		// XXX: TBD damaged warp coils
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

	function getTraktorShip() {
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

	public function isDestroyed() {
		return $this->getDestroyed() == 1;
	}

	public function getDestroyed() {
		return $this->destroyed;
	}

	private $destroyed = 0;

	public function setDestroyed($value) {
		$this->destroyed = $value;
	}

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
			$this->system = StarSystem::getSystemByCoords($this->getCX(),$this->getCY());
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

	function lowerShields($value) {
		$this->setShield($this->getShield()-$value);
	}
	
	function upperShields($value) {
		$this->setShield($this->getShield()+$value);
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

	function disableWeapons() {
		$this->setPhaser(0);
		$this->setTorpedos(0);
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

	function lowerEBatt($value) {
		$this->setEBatt($this->getEBatt()-$value);
	}
	
	function upperEBatt($value) {
		$this->setEBatt($this->getEBatt()+$value);
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
			$this->torpedo = new TorpedoType($this->getTorpedoType());
		}
		return $this->torpedo;
	} # }}}

	/**
	 */
	public function lowerTorpedo() { #{{{
		$this->setTorpedoCount($this->getTorpedoCount()-1);
		if ($this->getTorpedoCount() == 0) {
			$this->setTorpedos(0);			
		}
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

	private $damageTaken = 0;
	
	/**
	 */
	private function registerDamage($amount) { #{{{
		$this->damageTaken += $amount;
	} # }}}

	public function damageOLD(&$basedamage=0,$shieldDamageFactor=100,$hullDamageFactor=100,$phaser_damage=FALSE,$torpedo_damage=FALSE) {
		$this->setShieldRegenerationTimer(time());
		$msg = array();
		if ($this->shieldIsActive()) {
			$damage = round($basedamage/100*$shieldDamageFactor);
			$this->registerDamage($damage);
			if ($damage > $this->getShield()) {
				$msg[] = "- Schildschaden: ".$this->getShield();
				$msg[] = "-- Schilde brechen zusammen!";
				$damage -= $this->getShield();
				$this->setShieldState(0);
				$this->setShield(0);
				$basedamage = round($damage/$shieldDamageFactor*100);
			} else {
				$this->setShield($this->getShield()-$damage);
				$msg[] = "- Schildschaden: ".$damage." - Status: ".$this->getShield();
				$damage = 0;
				$basedamage = 0;
			}
		}
		if ($basedamage <= 0) {
			return $msg;
		}
		$disablemessage = FALSE;
		$damage = round($basedamage/100*$hullDamageFactor);
		// ablative huell plating
		if ($phaser_damage === TRUE && $this->getRump()->getRoleId() == ROLE_PHASERSHIP) {
			$damage = ceil($damage*0.6);
		}
		$this->registerDamage($damage); 
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
		$this->setDestroyed(1);
		return $msg;
	}

	/**
	 */
	public function damage(DamageWrapper $damage_wrapper) { #{{{
		$this->setShieldRegenerationTimer(time());
		$msg = array();
		if ($this->shieldIsActive()) {
			$damage = $damage_wrapper->getDamageRelative($this,DAMAGE_MODE_SHIELDS);
			$this->registerDamage($damage);
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
		$this->registerDamage($damage); 
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
		$this->setDestroyed(1);
		return $msg;
	} # }}}


	public function destroyTrumfield() {
		$this->remove();
	}

	function setRumpId($value) {
		$this->data['rumps_id'] = $value;
		$this->addUpdateField('rumps_id','getRumpId');
	}

	function selfDestroy() {
		HistoryEntry::addEntry('Die '.$this->getName().' hat sich in Sektor '.$this->getSectorString().' selbst zerstört');
		$this->destroy();
	}

	/**
	 */
	public function deactivateSystems() { #{{{
		$this->deactivateTraktorBeam();
		$this->setShieldState(0);
		$this->setNbs(0);
		$this->setLss(0);
		$this->disableWeapons();
	} # }}}


	public function destroy() {
		$this->deactivateSystems();
		$this->changeFleetLeader();
		$this->setFormerRumpsId($this->getRumpId());
		$this->setRumpId(TRUMFIELD_CLASS);
		$this->setHuell(round($this->getMaxHuell()/20));
		$this->setUserId(USER_NOONE);
		$this->setBuildplanId(0);
		$this->setShield(0);
		$this->setEps(0);
		$this->setFleetId(0);
		$this->setAlertState(1);
		$this->setWarpState(0);
		$this->setDock(0);
		$this->setName(_('Trümmer'));
		$this->setIsDestroyed(1);
		$this->cancelRepair();

		ShipSystems::truncate($this->getId());
		// TBD: Torpedos löschen

		$this->save();

		// clearing the cache
		$this->rump = NULL;
	}

	/**
	 */
	public function changeFleetLeader() { #{{{
		if ($this->isFleetLeader()) {
			$this->getFleet()->autochangeLeader($this);;
		}
	} # }}}

	/**
	 */
	public function remove() { #{{{
		$this->changeFleetLeader();
		ShipStorage::truncate($this->getId());
		ShipSystems::truncate($this->getId());
		ShipCrew::truncate('WHERE ships_id='.$this->getId());
		$this->deleteFromDatabase();
	} # }}}

	function getFlightDirection() {
		return $this->data['direction'];
	}

	function setFlightDirection($value) {
		$this->data['direction'] = $value;
		$this->addUpdateField('direction','getFlightDirection');
	}

	function leaveStarSystem() {
		$this->setWarpState(1);
		$this->setSystemsId(0);
		$this->setSX(0);
		$this->setSY(0);
	}

	function enterStarSystem(&$systemId,&$posx,&$posy) {
		$this->setWarpState(0);
		$this->setSystemsId($systemId);
		$this->setSX($posx);
		$this->setSY($posy);
		$this->save();
	}

	private $storage = NULL;

	public function getStorage() {
		if ($this->storage === NULL) {
			$this->storage = ShipStorage::getObjectsBy('ships_id='.$this->getId());
		}
		return $this->storage;
	}

	public function getStorageByGood($goodId) {
		if (!array_key_exists($goodId,$this->getStorage())) {
			return FALSE;
		}
		$stor = &$this->getStorage();
		return $stor[$goodId];
	}

	private $storageSum = NULL;

	function getStorageSum() {
		if ($this->storageSum === NULL) {
			$this->storageSum = DB()->query("SELECT SUM(count) FROM stu_ships_storage WHERE ships_id=".$this->getId(),1);
		}
		return $this->storageSum;
	}

	function setStorageSum($value) {
		$this->storageSum = $value;
	}

	function storagePlaceLeft() {
		return $this->getMaxStorage() > $this->getStorageSum();
	}

	function getMaxStorage() {
		return $this->getRump()->getStorage();
	}

	public function getCacheValue() {
		return "ship-".$this->getId()."-".currentUser()->getId()."-".$this->getLastModified();
	}

	public function lowerStorage($good_id,$count) {
		if (!$this->getStorage()->offsetExists($good_id)) {
			return;
		}
		if ($this->getStorage()->offsetGet($good_id)->getAmount() <= $count) {
			$this->getStorage()->offsetGet($good_id)->deleteFromDatabase();
			$this->getStorage()->offsetUnset($good_id);
			return;
		}
		$this->getStorage()->offsetGet($good_id)->lowerCount($count);
		$this->getStorage()->offsetGet($good_id)->save();
	}

	public function upperStorage($good_id,$count) {
		if (!$this->getStorage()->offsetExists($good_id)) {
			$this->getStorage()->offsetSet($good_id,new ShipStorageData());
			$this->getStorage()->offsetGet($good_id)->setShipId($this->getId());
			$this->getStorage()->offsetGet($good_id)->setGoodId($good_id);
		}
		$this->getStorage()->offsetGet($good_id)->upperCount($count);
		$this->getStorage()->offsetGet($good_id)->save();
	}

	private $currentcolony = NULL;

	function getCurrentColony() {
		if ($this->currentColony === NULL) {
			$this->currentColony = Colony::getBy("systems_id=".$this->getSystemsId()." AND sx=".$this->getPosX()." AND sy=".$this->getPosY());
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

	function enableSystemLeave() {
		if (!$this->isInSystem()) {
			return FALSE;
		}
		return TRUE;
	}

	function getDatabaseId() {
		return $this->data['database_id'];
	}

	function setDatabaseId($value) {
		$this->data['database_id'] = $value;
		$this->addUpdateField('database_id','getDatabaseId');
	}

	private $rump = NULL;

	function getRump() {
		if ($this->rump === NULL) {
			$this->rump = ResourceCache()->getObject('rump',$this->getRumpId());
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

	/**
	 */
	public function getTorpedoDamage() { #{{{
		$variance = round($this->getTorpedo()->getBaseDamage()/100*$this->getTorpedo()->getVariance());
		$basedamage= calculateModuleValue($this->getRump(),$this->getShipSystem(SYSTEM_TORPEDO)->getModule(),FALSE,$this->getTorpedo()->getBaseDamage());
		$damage = rand($basedamage-$variance,$basedamage+$variance);
		if (rand(1,100) <= $this->getTorpedo()->getCriticalChance()) {
			return $damage*2;
		}
		return $damage;
	} # }}}

	private $buildplan = NULL;

	public function getBuildplanId() {
		return $this->data['plans_id'];
	}

	public function setBuildplanId($value) {
		$this->setFieldValue('plans_id',$value,'getBuildplanId');
	}

	public function getBuildplan() {
		if ($this->buildplan === NULL) {
			$this->buildplan = new ShipBuildplans($this->getBuildplanId());
		}
		return $this->buildplan;
	}

	private $activeSystems = NULL;
	private $epsUsage = NULL;

	public function getEpsUsage() {
		if ($this->epsUsage === NULL) {
			$this->epsUsage = 0;
			foreach ($this->getActiveSystems() as $key => $obj) {
				$this->epsUsage += $obj->getEpsUsage();
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
			$this->systems = ShipSystems::getByShip($this->getId());
		}
		return $this->systems;
	}

	public function hasShipSystem($system) {
		return array_key_exists($system,$this->getSystems());
	}

	public function getShipSystem($system) {
		$arr = &$this->getSystems();
		return $arr[$system];
	}

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
		return $this->data[$system->getShipField()] >= 1;
	}

	public function getPhaserDamage() {
		if (!$this->hasShipSystem(SYSTEM_PHASER)) {
			return 0;
		}
		$basedamage= calculateModuleValue($this->getRump(),$this->getShipSystem(SYSTEM_PHASER)->getModule(),'getBaseDamage');
		$variance = round($basedamage/100*$this->getPhaser()->getVariance());
		$damage = rand($basedamage-$variance,$basedamage+$variance);
		if (rand(1,100) <= $this->getPhaser()->getCriticalChance()) {
			return $damage*2;
		}
		return $damage;
	}

	/**
	 */
	public function getTorpedoEpsCost() { #{{{
		// XXX: TBD
		return 1;
	} # }}}

	public function getPhaserEpsCost() {
		// XXX: TBD
		return 1;
	}

	private $phaser = NULL;

	/**
	 */
	public function getPhaser() { #{{{
		if ($this->phaser === NULL) {
			$this->phaser = Weapons::getByModuleId($this->getShipSystem(SYSTEM_PHASER)->getModuleId());
		}
		return $this->phaser;
	} # }}}

	protected function getLastModified() {
		return $this->data['lastmodified'];
	}

	protected function setLastModified() {
		$this->setFieldValue('lastmodified',time(),'getLastModified');
	}

	public function save() {
		$this->setLastModified();
		parent::save();
	}

	public function alertLevelBasedReaction() {
		$msg = array();
		if ($this->getCrew() == 0 || $this->getRump()->isTrumfield()) {
			return $msg;
		}
		if ($this->getAlertState() == ALERT_GREEN) {
			$this->setAlertState(ALERT_YELLOW);
			$msg[] = "- Erhöhung der Alarmstufe wurde durchgeführt";
		}
		if ($this->isDocked()) {
			$this->setDock(0);
			$msg[] = "- Das Schiff hat abgedockt";
		}
		if ($this->getWarpState() == 1) {
			$this->deactivateSystem(SYSTEM_WARPDRIVE);
			$msg[] = "- Der Warpantrieb wurde deaktiviert";
		}
		if ($this->cloakIsActive()) {
			$this->deactivateSystem(SYSTEM_CLOAK);
			$msg[] = "- Die Tarnung wurde deaktiviert";
		}
		if (!$this->shieldIsActive() && !$this->traktorBeamToShip() && $this->systemIsActivateable(SYSTEM_SHIELDS)) {
			if ($this->isTraktorbeamActive()) {
				$this->deactivateTraktorBeam();
				$msg[] = "- Der Traktorstrahl wurde deaktiviert";
			}
			$this->activateSystem(SYSTEM_SHIELDS);
			$msg[] = "- Die Schilde wurden aktiviert";
		}
		if ($this->systemIsActivateable(SYSTEM_NBS)) {
			$this->activateSystem(SYSTEM_NBS);
			$msg[] = "- Die Nahbereichssensoren wurden aktiviert";
		}
		if ($this->getAlertState() >= ALERT_YELLOW) {
			if ($this->systemIsActivateable(SYSTEM_PHASER)) {
				$this->activateSystem(SYSTEM_PHASER);
				$msg[] = "- Die Strahlenwaffe wurde aktiviert";
			}
		}
		return $msg;
	}

	public function systemIsActivateable($system) {
		if (!$this->hasShipSystem($system)) {
			return FALSE;
		}
		if (!$this->getShipSystem($system)->isActivateable()) {
			return FALSE;
		}
		if ($this->getShipSystem($system)->getEpsUsage() > $this->getEps()) {
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
			$this->lowerEps($this->getShipSystem($system)->getEpsUsage());
		}
	}

	public function deactivateSystem($system) {
		if (!$this->hasShipSystem($system)) {
			return;
		}
		$cb = $this->getShipSystem($system)->getShipCallback();
		$this->$cb(0);
	}

	public function canFire() {
		if ($this->getEps() == 0) {
			return FALSE;
		}
		if (!$this->nbsIsActive()) {
			return FALSE;
		}
		if (!$this->hasActiveWeapons()) {
			return FALSE;
		}
		return TRUE;
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
		return ceil($this->getCX()/MAPFIELDS_PER_SECTION);
	}

	public function getMapCY() {
		return ceil($this->getCY()/MAPFIELDS_PER_SECTION);
	}

	public function getCrewBySlot($slot) {
		return ShipCrew::getByShipSlot($this->getId(),$slot);
	}

	public function isCrewSlotSet($slot,$amount=1) {
		return count($this->getCrewBySlot($slot)) >= $amount;
	}

	public function getTradePostId() {
		return $this->data['trade_post_id'];
	}

	public function setTradePostId($value) {
		$this->setFieldValue('trade_post_id',$value,'getTradePostId');
	}

	public function isTradePost() {
		return $this->getTradePostId() > 0;
	}

	public function getDock() {
		return $this->data['dock'];
	}

	public function setDock($value) {
		$this->setFieldValue('dock',$value,'getDock');
	}

	public function isDocked() {
		return $this->getDock() > 0;
	}

	public function getDockedShip() {
		return ResourceCache()->getObject('ship',$this->getDock());
	}

	public function dockedOnTradePost() {
		return $this->isDocked() && $this->getDockedShip()->isTradePost();
	}

	private $dockPrivileges = NULL;

	public function getDockPrivileges() {
		if ($this->dockPrivileges === NULL) {
			$this->dockPrivileges = DockingRights::getConfigByShipId($this->getId());
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
			if (!$this->isInSystem()) {
				$this->mapfield = MapField::getFieldByCoords($this->getCX(),$this->getCY());
			} else {
				$this->mapfield = SystemMap::getFieldByCoords($this->getSystemsId(),$this->getSX(),$this->getSY());
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

	public function isDisabled() {
		return $this->getDisabled() > 0;
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
	public function regenerateShields(&$time) { #{{{
		if ($this->getCrewCount() < $this->getBuildplan()->getCrew()) {
			return;
		}
		$rate = $this->getShieldRegenerationRate();
		if ($this->getShield()+$rate > $this->getMaxShield()) {
			$rate = $this->getMaxShield()-$this->getShield();
		}
		$this->upperShields($rate);
		$this->setShieldRegenerationTimer($time);
		$this->save();
	} # }}}

	/**
	 */
	public function canIntercept() { #{{{
		return !$this->getTraktorMode();
	} # }}}

	/**
	 */
	public function deepDelete() { #{{{
		$this->deactivateTraktorBeam();
		ShipCrew::truncate('WHERE ships_id='.$this->getId());
		$this->remove();
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
		return $this->getCurrentColony()->hasAirfield();
	} # }}}

	/**
	 */
	public function canBeAttacked() { #{{{
		return !$this->ownedByCurrentUser() && !$this->getRump()->isTrumfield();
	} # }}}

	/**
	 */
	public function hasEscapePods() { #{{{
		return $this->getRump()->isTrumfield() && $this->getCrew() > 0;
	} # }}}

	/**
	 */
	public function currentUserCanMan() { #{{{
		return $this->ownedByCurrentUser() && $this->getBuildplan()->getCrew() > 0 && $this->getCrewCount() == 0;
	} # }}}

	/**
	 */
	public function currentUserCanUnMan() { #{{{
		return $this->ownedByCurrentUser() && $this->getCrewCount() > 0;
	} # }}}

	/**
	 */
	public function loadWarpCore($count) { #{{{
		foreach (array(GOOD_DEUTERIUM,GOOD_ANTIMATTER) as $key) {
			if (!$this->getStorage()->offsetExists($key)) {
				return FALSE;
			}
			if ($this->getStorage()->offsetGet($key)->getCount() < $count) {
				$count = $this->getStorage()->offsetGet($key)->getCount();
			}
		}
		$this->lowerStorage(GOOD_DEUTERIUM,$count);
		$this->lowerStorage(GOOD_ANTIMATTER,$count);
                if ($this->getWarpcoreLoad()+$count*WARPCORE_LOAD > $this->getWarpcoreCapacity()) {
			$load = $this->getWarpcoreCapacity() - $this->getWarpcoreLoad();
		} else {
			$load = $count*WARPCORE_LOAD;
		}
		$this->upperWarpcoreLoad($load);
		$this->save();

		return $load;
	} # }}}

	/**
	 */
	public function canLoadTorpedos() { #{{{
		if ($this->isDestroyed()) {
			return FALSE;
		}
		return $this->getMaxTorpedos();
	} # }}}

	private $torpedo_types = NULL;
	
	/**
	 */
	public function getPossibleTorpedoTypes() { #{{{
		if ($this->torpedo_types === NULL) {
			$this->torpedo_types = TorpedoType::getObjectsBy('WHERE level='.$this->getRump()->getTorpedoLevel());
		}
		return $this->torpedo_types;
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

	static public function copyShip($id) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id,4);
		$vars = array();
		$vals = array();
		foreach($result as $key => $value) {
			if ($key == 'id') {
				continue;
			}
			$vars[] = $key;
			$vals[] = "'".$result[$key]."'";
		}
		$newId = DB()->query("INSERT INTO ".self::tablename." (".join(",",$vars).") VALUES (".join(",",$vals).")",5);
		self::copyShipProperties($id,$newId);
		return new Ship($newId);
	}

	static public function copyShipProperties($oldId,$newId) {
		$result = DB()->query("SELECT * FROM stu_ships_systems WHERE ships_id=".$oldId);
		while ($data = mysqli_fetch_assoc($result)) {
			$vars = array();
			$vals = array();
			foreach($data as $key => $value) {
				if ($key == 'id') {
					continue;
				}
				if ($key == 'ships_id') {
					$data[$key] = $newId;
				}
				$vars[] = $key;
				$vals[] = "'".$data[$key]."'";
			}
			DB()->query("INSERT INTO stu_ships_systems (".join(",",$vars).") VALUES (".join(",",$vals).")",5);
		}

	}

	/**
	 */
	static function createBy($user_id,$rump_id,$buildplan_id,$colony=FALSE) { #{{{
		$ship = new ShipData;
		$ship->setUserId($user_id);
		$ship->setBuildplanId($buildplan_id);
		$ship->setRumpId($rump_id);
		for ($i=1;$i<=MODULE_TYPE_COUNT;$i++) {
			if ($ship->getBuildplan()->getModulesByType($i)) {
				$class = 'ModuleRumpWrapper'.$i;
				$wrapper = new $class($ship->getRump(),$ship->getBuildplan()->getModulesByType($i));
				foreach ($wrapper->getCallbacks() as $callback => $value) {
					$ship->$callback($value);
				}
			}
		}
		$ship->setMaxEbatt(round($ship->getMaxEps()/3));
		$ship->setName($ship->getRump()->getName());
		$ship->setSensorRange($ship->getRump()->getBaseSensorRange());
		$ship->save();
		if ($colony) {
			$ship->setSX($colony->getSX());
			$ship->setSY($colony->getSY());
			$ship->setSystemsId($colony->getSystemsId());
			$ship->setCX($colony->getSystem()->getCX(),TRUE);
			$ship->setCY($colony->getSystem()->getCY(),TRUE);
			$ship->save();
		}
		ShipSystems::createByModuleList($ship->getId(),BuildPlanModules::getByBuildplan($ship->getBuildplanId()));

		return $ship;
	} # }}}

	/**
	 */
	static function getCrewSumByUser($user_id) { #{{{
		return DB()->query('SELECT COUNT(id) FROM stu_ships_crew WHERE ships_id IN (SELECT id FROM '.self::tablename.' WHERE user_id='.$user_id.')',1);
	} # }}}

}
?>
