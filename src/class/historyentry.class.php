<?php

class HistoryEntryData extends BaseTable {

	const tablename = 'stu_history';
	protected $tablename = 'stu_history';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function getId() {
		return $this->data['id'];
	}

	function getType() {
		return $this->data['type'];
	}

	function getText() {
		return $this->data['text'];
	}

	function setText($value) {
		$this->data['text'] = $value;
		$this->addUpdateField('text','getText');
	}

	function setType($value) {
		$this->data['type'] = $value;
		$this->addUpdateField('type','getType');
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	function setDate($value) {
		$this->data['date'] = $value;
		$this->addUpdateField('date','getDate');
	}

	function getDate() {
		return $this->data['date'];
	}
}
class HistoryEntry extends HistoryEntryData {
	
	static function getListBy($sql) {
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new HistoryEntryData($data);
		}
		return $ret;
	}

	static function countInstances($sql) {
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." ".$sql,1);
	}

	static function addEntry($txt,$userId=USER_NOONE) {
		$entry = new HistoryEntryData;
		$entry->setText($txt);
		$entry->setUserId($userId);
		$entry->setDate(time());
		$entry->save();
	}

	static function addAllianceEntry($txt,$userId=USER_NOONE) {
		$entry = new HistoryEntryData;
		$entry->setText($txt);
		$entry->setUserId($userId);
		$entry->setDate(time());
		$entry->setType(HISTORY_ALLIANCE);
		$entry->save();
	}

}
?>
