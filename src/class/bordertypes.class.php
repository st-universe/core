<?php

class BordertypesData extends BaseTable {

	const TABLENAME = 'stu_map_bordertypes';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getFactionId() {
		return $this->data['faction_id'];
	}

	function setFactionId($value) {
		$this->data['faction_id'] = $value;
		$this->addUpdateField('faction_id','getFactionId');
	}

	function getColor() {
		return $this->data['color'];
	}

	function getDescription() {
		return $this->data['description'];
	}

	function getTable() {
		return self::TABLENAME;
	}

}
class Bordertypes extends BordertypesData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getList($sql='') {
		$where = '';
		if ($sql) {
			$where = ' WHERE '.$sql;
		}
		$ret = array();
		$result = DB()->query("SELECT * FROM ".self::getTable().$where);
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new BordertypesData($data);
		}
		return $ret;
	}
}
?>
