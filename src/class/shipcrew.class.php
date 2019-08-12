<?php
class ShipCrewData extends BaseTable {

	protected $tablename = 'stu_ships_crew';
	const tablename = 'stu_ships_crew';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

	public function getCrewId() {
		return $this->data['crew_id'];
	}

	public function setCrewId($value) {
		$this->setFieldValue('crew_id',$value,'getCrewId');
	}

	public function getShipId() {
		return $this->data['ships_id'];
	}

	public function getCrew() {
		return Crew::getById($this->getCrewId());	
	}

	public function setShipId($value) {
		$this->setFieldValue('ships_id',$value,'getShipId');
	}

	public function getSlot() {
		return $this->data['slot'];
	}

	public function setSlot($value) {
		$this->setFieldValue('slot',$value,'getSlot');
	}

	/**
	 */
	public function setUserId($value) { # {{{
		$this->setFieldValue('user_id',$value,'getUserId');
	} # }}}

	/**
	 */
	public function getUserId() { # {{{
		return $this->data['user_id'];
	} # }}}

}
class ShipCrew extends ShipCrewData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);	
		if ($result == 0) {
			throw new ObjectNotFoundException($ship_id);
		}
		parent::__construct($result);
		return self::_getBy($result,$id,'ShipCrewData');
	}

	static public function getByShip($shipId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE ships_id=".intval($shipId)." ORDER BY slot");	
		return self::_getList($result,'ShipCrewData');
	}

	static public function getByShipSlot($shipId,$slot) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE ships_id=".intval($shipId)." AND slot=".$slot);	
		return self::_getList($result,'ShipCrewData');
	}

	/**
	 */
	static function countInstances($qry="") { #{{{
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." ".$qry,1);
	} # }}}

	static public function getByCrewId($crewId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE crew_id=".intval($crewId)." LIMIT 1",4);	
		if ($result == 0) {
			return FALSE;
		}
		return new ShipCrewData($result);
	}

	/**
	 */
	static function createByRumpCategory(ShipData $ship) { #{{{
		for ($i=CREW_TYPE_FIRST;$i<=CREW_TYPE_LAST;$i++) {
			$j = 1;
			if ($i == CREW_TYPE_CREWMAN) {
				$slot = 'getJob'.$i.'Crew'.$ship->getBuildPlan()->getCrewPercentage();
			} else {
				$slot = 'getJob'.$i.'Crew';
			}
			$config = RumpCatRoleCrew::getByRumpCatRole($ship->getRump()->getCategoryId(),$ship->getRump()->getRoleId());
			while ($j<=$config->$slot()) {
				$j++;
				if (($crew=Crew::getFreeCrewByTypeAndUser($ship->getUserId(),$i)) === FALSE) {
					$crew = Crew::getFreeCrewByTypeAndUser($ship->getUserId());
				}
				$sc = new ShipCrewData;
				$sc->setCrewId($crew->getId());
				$sc->setShipId($ship->getId());
				$sc->setUserId($ship->getUserId());
				$sc->setSlot($i);
				$sc->save();
			}
		}
	} # }}}

	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}

}
?>
