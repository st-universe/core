<?php
class RPGPlotMemberData extends BaseTable {

	protected $tablename = 'stu_plots_members';
	const tablename = 'stu_plots_members';

	function __construct($data=array()) {
		$this->data = &$data;
	}

	function getId() {
		return $this->data['id'];
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getUserId() {
		return $this->data['user_id'];
	}

	function setUserId($value) {
		$this->data['user_id'] = $value;
		$this->addUpdateField('user_id','getUserId');
	}

	function getPlotId() {
		return $this->data['plot_id'];
	}

	function setPlotId($value) {
		$this->data['plot_id'] = $value;
		$this->addUpdateField('plot_id','getPlotId');
	}
}
class RPGPlotMember extends RPGPlotMemberData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".parent::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function countInstances($qry) {
		return DB()->query("SELECT COUNT(id) FROM ".parent::tablename." WHERE ".$qry,1);
	}

	static function mayWriteStory($plotId,$userId) {
		return DB()->query("SELECT id FROM ".parent::tablename." WHERE plot_id=".$plotId." AND user_id=".$userId,1);
	}

	static function getPlotsByUser($userId) {
		$ret = array();
		$result = DB()->query("SELECT b.* FROM ".parent::tablename." as a LEFT JOIN stu_plots as b ON b.id=a.plot_id WHERE a.user_id=".intval($userId));
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new RPGPlotData($data);
		}
		return $ret;
	}

	/**
	 */
	static function findObject($sql='') { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' '.$sql.' LIMIT 1',4);
		if ($result == 0) {
			return FALSE;
		}
		return new RPGPlotMemberData($result);
	} # }}}


}
?>
