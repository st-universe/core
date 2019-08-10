<?php

class GameConfigData extends BaseTable {

	protected $tablename = 'stu_game_config';
	const tablename = 'stu_game_config';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	public function getOption() {
		return $this->data['option'];
	}

	public function setOption($value) {
		$this->setFieldValue('option',$value,'getOption');
	}

	public function getValue() {
		return $this->data['value'];
	}

	public function setValue($value) {
		$this->setFieldValue('value',$value, 'getValue');
	}
}

class GameConfig extends GameConfigData {

	function __construct($configId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$configId." LIMIT 1",4);
		if ($result == 0) {
			throw new ObjectNotFoundException($configId);
		}
		parent::__construct($result);
	}

	static function getObjectsBy($where="") {
		$result = DB()->query("SELECT * FROM ".self::tablename.$where);
		return self::_getList($result,'GameConfigData','option');
	}

	static public function getObjectByOption($option) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE `option`=".intval($option)." LIMIT 1",4);
		if ($result == 0) {
			throw new ObjectNotFoundException($option);
		}
		return new GameConfig($result['id']);
	}
}
?>
