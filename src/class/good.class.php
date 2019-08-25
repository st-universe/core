<?php

class GoodData extends BaseTable {

	const tablename = 'stu_goods';
	protected $tablename = 'stu_goods';
	
	const NAHRUNG = 1;
	const BAUMATERIAL = 2;

	function __construct(&$result) {
		$this->data = $result;
	}

	/**
	 */
	function setId($value) { #{{{
		$this->data['id'] = $value;
		parent::setId($value);
	} # }}}

	/**
	 */
	public function getId() { # {{{
		return $this->data['id'];
	} # }}}

	/**
	 */
	public function setName($value) { # {{{
		$this->setFieldValue('name',$value,'getName');
	} # }}}

	/**
	 */
	public function getName() { # {{{
		return $this->data['name'];
	} # }}}

	function getAmount() {
		return $this->data['count'];
	}

	function isTradeable() {
		return $this->data['tradeable'];
	}

	function isBeamable() {
		return $this->data['beamable'];
	}

	function isTorpedo() {
		return $this->data['is_torpedo'];
	}

	public function isIllegal($network) {
		return $this->data['illegal_'.$network] == 1;
	}

	function isIllegalFoed() {
		return $this->data['illegal_1'];
	}

	function isIllegalRom() {
		return $this->data['illegal_2'];
	}

	function isIllegalKling() {
		return $this->data['illegal_3'];
	}

	function isIllegalCard() {
		return $this->data['illegal_4'];
	}

	function isIllegalFerf() {
		return $this->data['illegal_5'];
	}

	function getTransferCount() {
		// TBD Anzahl Waren pro Energie
		// Möglicherweise einstellbar nach Warentyp
		return 10;
	}

	function setCount($value) {
		$this->data['count'] = $value;
	}

	function lowerCount($value) {
		$this->setCount($this->getAmount()-$value);
	}

	function upperCount($value) {
		$this->setCount($this->getAmount()+$value);
	}

	/**
	 */
	public function setType($value) { # {{{
		$this->setFieldValue('type',$value,'getType');
	} # }}}

	/**
	 */
	public function getType() { # {{{
		return $this->data['type'];
	} # }}}
	
	/**
	 */
	public function isSaveable() { #{{{
		return $this->getType() != GOOD_TYPE_EFFECT;
	} # }}}

}
class Good extends GoodData {

	function __construct($goodId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$goodId." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($goodId);
		}
		return parent::__construct($result);
	}

	static function getGoodsByShip($ship_id=0) {
		$result = DB()->query("SELECT a.count,b.* FROM stu_ships_storage as a LEFT JOIN stu_goods as b ON b.id=a.goods_id WHERE a.ships_id=".intval($ship_id));
		$ret = array();
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[$data['id']] = new GoodData($data);
		}
		return $ret;
	}

	static function getGoodsBy($qry) {
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$qry);
		return self::_getList($result,'GoodData');
	}

	static function getGoodsByColony($colony_id=0) {
		$result = DB()->query("SELECT a.count,b.* FROM stu_colonies_storage as a LEFT JOIN stu_goods as b ON b.id=a.goods_id WHERE a.colonies_id=".intval($colony_id));
		$ret = array();
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[$data['id']] = new GoodData($data);
		}
		return $ret;
	}

	static public function getById($goodId) {
		return ResourceCache()->getObject("good",$goodId);
	}

	static function getList($qry=FALSE) {
		if ($qry) {
			$qry = " WHERE ".$qry;
		}
		$result = DB()->query("SELECT * FROM ".self::tablename.$qry." ORDER BY sort");
		$ret = array();
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[$data['id']] = new GoodData($data);
		}
		return $ret;
	}

	static public function getListByActiveBuildings($colonyId) {
		$ret = array();
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id IN (SELECT goods_id FROM stu_buildings_goods WHERE buildings_id IN (SELECT buildings_id FROM stu_colonies_fielddata WHERE colonies_id=".$colonyId.")) GROUP BY id");
		while($data = mysqli_fetch_assoc($result)) {
			$ret[$data['id']] = new GoodData($data);
		}
		return $ret;
	}

}
?>
