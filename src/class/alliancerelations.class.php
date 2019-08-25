<?php

class AllianceRelationData extends BaseTable {

	const TABLENAME = 'stu_alliances_relations';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function getTable() {
		return self::TABLENAME;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getAllianceId() {
		return $this->data['alliance_id'];
	}

	function setAllianceId($value) {
		$this->data['alliance_id'] = $value;
		$this->addUpdateField('alliance_id','getAllianceId');
	}

	function getRecipientId() {
		return $this->data['recipient'];
	}

	function setRecipientId($value) {
		$this->data['recipient'] = $value;
		$this->addUpdateField('recipient','getRecipientId');
	}

	private $recipient = NULL;

	function getRecipient() {
		if ($this->recipient === NULL) {
			$this->recipient = new Alliance($this->getRecipientId());
		}
		return $this->recipient;
	}

	private $alliance = NULL;

	function getAlliance() {
		if ($this->alliance === NULL) {
			$this->alliance = new Alliance($this->getAllianceId());
		}
		return $this->alliance;
	}

	function getType() {
		return $this->data['type'];
	}

	function getTypeDescription() {
		switch ($this->getType()) {
			case ALLIANCE_RELATION_WAR:
				return 'Krieg';
			case ALLIANCE_RELATION_PEACE:
				return 'Friedensabkommen';
			case ALLIANCE_RELATION_FRIENDS:
				return 'Freundschaftabkommen';
			case ALLIANCE_RELATION_ALLIED:
				return 'Bündnis';
		}
	}

	function setType($value) {
		$this->data['type'] = $value;
		$this->addUpdateField('type','getType');
	}

	function getDate() {
		return $this->data['date'];
	}

	function setDate($value) {
		$this->data['date'] = $value;
		$this->addUpdateField('date','getDate');
	}

	function isPending() {
		return $this->getDate() == 0;
	}

	/**
	 */
	public function cycleOpponents() { #{{{
		$alliance = $this->getAlliance();
		$recipient = $this->getRecipient();
		$this->alliance = $recipient;
		$this->recipient = $alliance;
	} # }}}

	function getOpponent() {
		return $this->getRecipient();
	}

	function isWar() {
		return $this->getType() == ALLIANCE_RELATION_WAR;
	}

	function getPossibleTypes() {
		$ret = array();
		if ($this->getType() != ALLIANCE_RELATION_FRIENDS) {
			$ret[] = array("name" => "Freundschaft","value" => ALLIANCE_RELATION_FRIENDS);
		}
		if ($this->getType() != ALLIANCE_RELATION_ALLIED) {
			$ret[] = array("name" => "Bündnis","value" => ALLIANCE_RELATION_ALLIED);
		}
		return $ret;
	}

	function offerIsSend() {
		return $this->getAllianceId() == currentUser()->getAllianceId();
	}

}
class AllianceRelation extends AllianceRelationData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getList($sql) {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE ".$sql);
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[] = new AllianceRelationData($data);
		}
		return $ret;
	}

	static function getBy($sql) {
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE ".$sql." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new AllianceRelationData($result);
	}

	static function countInstances($sql) {
		return DB()->query("SELECT * FROM ".self::getTable()." WHERE ".$sql,1);
	}

	static function isValidRelationType($type) {
		$types = array(ALLIANCE_RELATION_WAR => 1,
			       ALLIANCE_RELATION_PEACE => 1,
		       	       ALLIANCE_RELATION_FRIENDS => 1,
		       	       ALLIANCE_RELATION_ALLIED => 1);
		return array_key_exists($type,$types);
	}
	
	static function getById(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE id=".intval($id)." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new AllianceRelationData($result);
	}

	static function truncateBy($sql) {
		DB()->query("DELETE FROM ".self::getTable()." WHERE ".$sql);
	}

}
?>
