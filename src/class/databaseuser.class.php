<?php

class DatabaseUserData extends BaseTable {

	protected $tablename = 'stu_database_user';
	const tablename = 'stu_database_user';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getDatabaseId() {
		return $this->data['database_id'];
	}

	function setDatabaseId($value) {
		$this->data['database_id'] = $value;
		$this->addUpdateField('database_id','getDatabaseId');
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	function getDate() {
		return $this->data['date'];
	}

	function setDate($value) {
		$this->data['date'] = $value;
		$this->addUpdateField('date','getDate');
	}

	function getDateDisplay() {
		return parseDateTime($this->getDate());
	}
}

class DatabaseUser extends DatabaseUserData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".$this->getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function checkEntry($databaseId,$userId) {
		if (DB()->query("SELECT * FROM ".parent::tablename." WHERE database_id=".$databaseId." AND user_id=".$userId,1)) {
			return TRUE;
		}
		return FALSE;
	}

	static function getBy($databaseId,$userId) {
		$result = DB()->query("SELECT * FROM ".parent::tablename." WHERE database_id=".$databaseId." AND user_id=".$userId." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new DatabaseUserData($result);
	}

	static function addEntry($databaseId,$userId) {
		$obj = new DatabaseUserData;
		$obj->setDatabaseId($databaseId);
		$obj->setUserId($userId);
		$obj->setDate(time());
		$obj->save();
	}

	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}

}
?>
