<?php

class FactionData extends BaseTable {

	protected $tablename = 'stu_factions';
	const tablename = 'stu_factions';
	
	public $data = array();

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getName() {
		return $this->data['name'];
	}

	function getDescription() {
		return $this->data['description'];
	}

	function getDescriptionDecoded() {
		return decodeString($this->getDescription());
	}

	function getDescriptionDisplay() {
		return nl2br($this->getDescriptionDecoded());
	}

	function getDarkerColor() {
		return $this->data['darker_color'];
	}

	/**
	 */
	public function setPlayerLimit($value) { # {{{
		$this->setFieldValue('player_limit',$value,'getPlayerLimit');
	} # }}}

	/**
	 */
	public function getPlayerLimit() { # {{{
		return $this->data['player_limit'];
	} # }}}
	
	/**
	 */
	public function setChooseable($value) { # {{{
		$this->setFieldValue('chooseable',$value,'getChooseable');
	} # }}}

	/**
	 */
	public function getChooseable() { # {{{
		return $this->data['chooseable'];
	} # }}}

	private $player_amount = NULL;

	/**
	 */
	public function getPlayerAmount() { #{{{
		if ($this->player_amount === NULL) {
			$this->player_amount = self::getCount('stu_user','race='.$this->getId());
		}
		return $this->player_amount;
	} # }}}

	/**
	 */
	public function hasFreePlayerSlots() { #{{{
		return $this->getPlayerLimit() == 0 || $this->getPlayerAmount() < $this->getPlayerLimit();
	} # }}}

	public function getBuildingId() {
		return $this->data['buildings_id'];
	}

	public function setBuildingId($value) {
		$this->setFieldValue('buildings_id',$value,'getBuildingId');
	}

}
class Faction extends FactionData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".parent::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getObjectsBy($qry) {
		if (strlen($qry) == 0) {
			$qry = '';
		} else {
			$qry = ' WHERE '.$qry;
		}
		$ret = array();
		$result = DB()->query("SELECT * FROM ".parent::tablename.$qry);
		while($data = mysqli_fetch_assoc($result)) {
			$ret[$data['id']] = new FactionData($data);
		}
		return $ret;
	}

	static function getChooseableFactions() {
		return self::getObjectsBy('chooseable=1 ORDER BY id');
	}

	static function getById($factionId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".intval($factionId)." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new FactionData($result);
	}

}
?>
