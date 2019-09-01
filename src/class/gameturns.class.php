<?php

class GameTurnData extends BaseTable {

	protected $tablename = 'stu_game_turns';
	const tablename = 'stu_game_turns';
	
	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getEnd() {
		return $this->data['end'];
	}

	function setEnd($value) {
		$this->data['end'] = $value;
		$this->addUpdateField('end','getEnd');
	}

	function getStart() {
		return $this->data['start'];
	}

	function setStart($value) {
		$this->data['start'] = $value;
		$this->addUpdateField('start','getStart');
	}

	function getTurn() {
		return $this->data['turn'];
	}

	function setTurn($value) {
		$this->data['turn'] = $value;
		$this->addUpdateField('turn','getTurn');
	}

	function getNextTurn() {
		return $this->getTurn()+1;
	}

}
class GameTurn extends GameTurnData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".$this->getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return parent::__construct($result);
	}

	static function getCurrentTurn() {
		$result = DB()->query("SELECT * FROM ".parent::tablename." ORDER BY turn DESC LIMIT 1",4);
		return new GameTurnData($result);
	}
}

?>
