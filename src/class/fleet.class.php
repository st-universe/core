<?php

class FleetData extends BaseTable {

	const tablename = 'stu_fleets';
	protected $tablename = 'stu_fleets';

	private $shiplist = NULL;
	private $shipCount = NULL;

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	public function getShips() {
		if ($this->shiplist === NULL) {
			$this->shiplist = Ship::getObjectsBy("WHERE fleets_id=".$this->getFleetId()." ORDER BY is_base DESC, id LIMIT 200");
		}
		return $this->shiplist;
	}

	function getShipCount($cache=TRUE) {
		if ($this->shipCount === NULL || $cache === FALSE) {
			$this->shipCount = Ship::countInstances("WHERE fleets_id=".$this->getFleetId());
		}
		return $this->shipCount;
	}

	public function getFleetId() {
		return $this->getId();
	}

	public function getId() {
		return $this->data['id'];
	}

	function getFleetLeader() {
		return $this->data['ships_id'];
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	public function ownedByCurrentUser() {
		return currentUser()->getId() == $this->getUserId();
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	function setFleetLeader($value) {
		$this->data['ships_id'] = $value;
		$this->addUpdateField('ships_id','getFleetLeader');
	}

	function setFleetId($value) {
		$this->data['fleets_id'] = $value;
		$this->addUpdateField('fleets_id','getId');
	}

	function setShipsId($value) {
		$this->data['ships_id'] = $value;
		$this->addUpdateField('ships_id','getFleetLeader');
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
		$this->addUpdateField('name','getName');
	}

	function getName() {
		return $this->data['name'];
	}

	function getNameWithoutMarkup() {
	        return BBCode()->parse($this->getName())->getAsText();
	}

	function deleteFromDb() {
		parent::deleteFromDatabase();
		DB()->query("UPDATE stu_ships SET fleets_id=0 WHERE fleets_id=".$this->getId());
	}

	private $fleetLeader = NULL;

	function getLeadShip() {
		if ($this->fleetLeader === NULL) {
			$this->fleetLeader = new Ship($this->getFleetLeader());
		}
		return $this->fleetLeader;
	}

	private $availableShips = NULL;

	public function getAvailableShips() {
		if ($this->availableShips === NULL) {
			$this->availableShips = Ship::getObjectsBy("WHERE user_id=".currentUser()->getId()." AND fleets_id=0
				       AND ((systems_id=0 AND cx=".$this->getLeadShip()->getCX()." AND cy=".$this->getLeadShip()->getCY().") OR
				       (systems_id>0 AND sx=".$this->getLeadShip()->getSX()." AND sy=".$this->getLeadShip()->getSY()." AND
				       systems_id=".$this->getLeadShip()->getSystemsId().")) AND id!=".$this->getLeadShip()->getId()." AND is_base=0");
		}
		return $this->availableShips;
	}

	function autochangeLeader(Ship $obj) {
		$ship = Ship::getObjectBy("WHERE fleets_id=".$this->getId()." AND id!=".$obj->getId());
		if (!$ship) {
			$this->deleteFromDatabase();
			$obj->setFleetId(0);
			return;	
		}
		$this->setFleetLeader($ship->getId());
		$this->fleetLeader = NULL;
		$this->save();
	}

	public function deactivateSystem($system) {
		foreach ($this->getShips() as $key => $ship) {
			$ship->deactivateSystem($system);
			$ship->save();
		}	
	}

	public function activateSystem($system) {
		foreach ($this->getShips() as $key => $ship) {
			$ship->activateSystem($system);
			$ship->save();
		}	
	}

	public function getUser() {
		return ResourceCache()->getObject('user',$this->getUserId());
	}

	private $pointsum = NULL;

	/**
	 */
	public function getPointSum() { #{{{
		if ($this->pointsum === NULL) {
			$this->pointsum = DB()->query('SELECT SUM(c.points) FROM stu_ships as a LEFT JOIN stu_rumps as b ON (b.id=a.rumps_id) LEFT JOIN stu_rumps_categories as c ON (c.id=b.category_id) WHERE a.fleets_id='.$this->getId(),1);
		}
		return $this->pointsum;
	} # }}}

}

class Fleet extends FleetData {

	function __construct($fleet_id = 0) {
		$result = DB()->query("SELECT * FROM stu_fleets WHERE id=".$fleet_id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($fleet_id);
		}
		parent::__construct($result);
	}

	/**
	 * @return ShipData[]
	 */
	static function getShipsBy(&$fleetId,$without=array(0)) {
		$ret = array();
		$result = DB()->query("SELECT * FROM stu_ships WHERE fleets_id=".$fleetId." AND id NOT IN (".join(",",$without).") ORDER BY id DESC,is_base DESC, id LIMIT 200");
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new ShipData($data);
		}
		return $ret; 
	}

	static public function getRandomShipById(&$fleetId) {
		$result = DB()->query("SELECT * FROM stu_ships WHERE fleets_id=".$fleetId." ORDER BY RAND() LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new ShipData($result);
	}

	static function getFleetsByUser(&$user_id) {
		$ret = array();
		$result = DB()->query("SELECT * FROM stu_fleets WHERE user_id=".$user_id);
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new FleetData($data);
		}
		return $ret;
	}

	static function getUserFleetById($fleet_id, int $userId) {
		$result = DB()->query("SELECT * FROM stu_fleets WHERE id=".$fleet_id." AND user_id=".$userId." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($fleet_id);
		}
		return new FleetData($result);
	}

	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}

}
?>
