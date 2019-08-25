<?php
class MapFieldData extends BaseTable {

	protected $tablename = 'stu_map';
	const tablename = 'stu_map';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function getEpsCost() {
		return $this->data['ecost'];
	}

	function getDamage() {
		return $this->data['damage'];
	}

	function getSpecialDamage() {
		return $this->data['x_damage'];
	}

	function specialDamageInnerSystemOnly() {
		return $this->data['x_damage_system'];
	}

	function getName() {
		return $this->data['name'];
	}

	function getType() {
		if ($this->getHide()) {
			return 0;
		}
		return $this->data['type'];
	}

	function getFieldId() {
		return $this->data['field_id'];
	}

	function setFieldId($value) {
		$this->data['field_id'] = $value;
		$this->addUpdateField('field_id','getFieldId');
	}

	function setCX($value) {
		$this->data['cx'] = $value;
		$this->addUpdateField('cx','getCX');
	}

	function getCX() {
		return $this->data['cx'];
	}

	function setCY($value) {
		$this->data['cy'] = $value;
		$this->addUpdateField('cy','getCY');
	}

	function getCY() {
		return $this->data['cy'];
	}

	function setBordertype($value) {
		$this->data['bordertype_id'] = $value;
		$this->addUpdateField('bordertype_id','getBordertype');
	}

	function getBordertype() {
		return $this->data['bordertype_id'];
	}

	function getBorder() {
		if ($this->getBordertype() == 0) {
			return '';
		}
		$border = getBorderType($this->getBorderType());
		return 'border: 1px solid #'.$border->getColor();
	}

	function getFieldStyle() {
		if ($this->getHide()) {
			$type = 0;
		} else {
			$type = getMapType($this->getFieldId())->getType();
		}
		$style = "background-image: url('assets/map/".$type.".gif');";
		$style .= $this->getBorder();
		return $style;
	}

	private $hide = FALSE;

	public function getHide() {
		return $this->hide;
	}

	public function setHide($value) {
		$this->hide = $value;
	}

	public function getRegionId() {
		return $this->data['region_id'];
	}

	public function setRegionId($value) {
		$this->setFieldValue('region_id',$value,'getRegionId');
	}

	private $region = NULL;

	public function getMapRegion() {
		if ($this->region === NULL) {
			$this->region = new MapRegion($this->getRegionId());
		}
		return $this->region;
	}

	public function hasRegion() {
		return $this->getRegionId() > 0;
	}

	public function getFieldType() {
		return ResourceCache()->getObject('mapfield',$this->getFieldId());
	}

	private $system = NULL;

	/**
	 */
	public function getSystem() { #{{{
		if (!$this->getFieldType()->getIsSystem()) {
			return FALSE;
		}
		if ($this->system === NULL) {
			$this->system = StarSystem::getSystemByCoords($this->getCX(),$this->getCY());
		}
		return $this->system;
	} # }}}

} 
class MapField extends MapFieldData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM stu_map WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getFieldsByFlightRoute($sx,$sy,$ex,$ey) {
		$ret = array();
		if ($sy > $ey) {
			$oy = $sy;
			$sy = $ey;
			$ey = $oy;
		}
		if ($sx > $ex) {
			$ox = $sx;
			$sx = $ex;
			$ex = $ox;
		}
		$result = DB()->query("SELECT * FROM stu_map WHERE cx BETWEEN ".$sx." AND ".$ex." AND cy BETWEEN ".$sy." AND ".$ey);
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[$data['cx']."_".$data['cy']] = new MapFieldData($data);
		}
		return $ret;
	}

	static function getFieldByCoords($x,$y) {
		$result = DB()->query("SELECT * FROM stu_map WHERE cx=".intval($x)." AND cy=".intval($y)." LIMIT 1",4);
		if ($result == 0) {
			if ($x >= 1 && $x <= MAP_MAX_X && $y >= 1 && $y <= MAP_MAX_Y) {
				$field = new MapFieldData();
				$field->setCX($x);
				$field->setCY($y);
				$field->setType(1);
				$field->save();
				return $field;
			}
			return FALSE;
		}
		return new MapFieldData($result);
	}

	static function getUserFieldsByRange($xStart,$xEnd,$y) {
		$ret = array();
		$result = DB()->query("SELECT a.*,b.user_id as hide FROM stu_map as a LEFT JOIN stu_user_map as b ON b.cx=a.cx AND b.cy=a.cy AND b.user_id=".currentUser()->getId()." WHERE a.cx BETWEEN ".intval($xStart)." AND ".intval($xEnd)." AND a.cy=".intval($y)." ORDER BY a.cx");
		while($data=mysqli_fetch_assoc($result)) {
			$ret[$data['cx']] = new MapFieldData($data);
			if (currentUser()->getMapType() == MAPTYPE_INSERT) {
				if (!array_key_exists('hide',$data) || !$data['hide'] || $data['hide'] == '') {
					$ret[$data['cx']]->setHide(TRUE);
				}
			} else {
				if (array_key_exists('hide',$data) && $data['hide'] > 0) {
					$ret[$data['cx']]->setHide(TRUE);
				}
			}
		}
		return $ret;
	}

	static public function getListBy($sql) {
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'MapfieldData');
	}

	static function getFieldsBy($sql) {
		return DB()->query("SELECT * FROM stu_map WHERE ".$sql." ORDER BY cy,cx");
	}

	static public function countInstances($sql) {
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." ".$sql,1);
	}
}
?>
