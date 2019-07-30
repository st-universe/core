<?php

class PMCategoryData extends BaseTable {

	const tablename = 'stu_pm_cats';
	protected $tablename = 'stu_pm_cats';

	function __construct($data) {
		$this->data = $data;
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

	function getDescription() {
		return $this->data['description'];
	}

	function setDescription($value) {
		$this->data['description'] = encodeString($value);
		$this->addUpdateField('description','getDescription');
	}

	function getDescriptionDecoded() {
		return stripslashes(decodeString($this->getDescription()));
	}

	public function getDescriptionDecodedRaw() {
		return stripslashes(decodeString($this->getDescription(),FALSE));
	}

	function getSort() {
		return $this->data['sort'];
	}

	function setSort($value) {
		$this->data['sort'] = $value;
		$this->addUpdateField('sort','getSort');
	}

	function getSpecial() {
		return $this->data['special'];
	}

	function setSpecial($value) {
		$this->data['special'] = $value;
		$this->addUpdateField('special','getSpecial');
	}
	
	private $pmcount = NULL;

	function getCategoryCount() {
		if ($this->pmcount === NULL) {
			$this->pmcount = DB()->query("SELECT COUNT(id) FROM stu_pms WHERE cat_id=".$this->getId(),1);
		}
		return $this->pmcount;
	}

	private $newpmcount = NULL;

	function getCategoryCountNew() {
		if ($this->newpmcount === NULL) {
			$this->newpmcount = DB()->query("SELECT COUNT(id) FROM stu_pms WHERE new=1 AND cat_id=".$this->getId(),1);
		}
		return $this->newpmcount;
	}

	function hasNewPMs() {
		return $this->getCategoryCountNew() > 0;
	}

	function getMaxSorting() {
		return DB()->query("SELECT MAX(sort) FROM stu_pm_cats WHERE user_id=".$this->getUserId(),1);
	}

	function appendToSorting() {
		$sort = $this->getMaxSorting();
		$this->setSort($sort+1);
	}

	function isOwnCategory() {
		return $this->getUserId() == currentUser()->getId();
	}

	function isPMOutDir() {
		return $this->getSpecial() == PM_SPECIAL_PMOUT;
	}

	function isDropable() {
		switch ($this->getSpecial()) {
			case PM_SPECIAL_SHIP:
			case PM_SPECIAL_COLONY:
			case PM_SPECIAL_TRADE:
			case PM_SPECIAL_PMOUT:
				return FALSE;
		}
		return TRUE;
	}

	function isDeleteAble() {
		return $this->getSpecial() == 0;
	}
	
	function truncate() {
		DB()->query("DELETE FROM stu_pms WHERE cat_id=".$this->getId());
	}

	/**
	 */
	public function deepDelete() { #{{{
		$this->truncate();
		$this->deleteFromDatabase();
	} # }}}

}
class PMCategory extends PMCategoryData {

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".$this->getTable()." WHERE id=".intval($id)." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		parent::__construct($result);
	}

	static function getCategoryTree() {
		$ret = array();
		$cat = self::getOrGenSpecialCategory(PM_SPECIAL_MAIN,currentUser()->getId());
		$ret[$cat->getId()] = $cat;
		$cat = self::getOrGenSpecialCategory(PM_SPECIAL_SHIP,currentUser()->getId());
		$ret[$cat->getId()] = $cat;
		$cat = self::getOrGenSpecialCategory(PM_SPECIAL_COLONY,currentUser()->getId());
		$ret[$cat->getId()] = $cat;
		$cat = self::getOrGenSpecialCategory(PM_SPECIAL_TRADE,currentUser()->getId());
		$ret[$cat->getId()] = $cat;
		$cat = self::getOrGenSpecialCategory(PM_SPECIAL_PMOUT,currentUser()->getId());
		$ret[$cat->getId()] = $cat;
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE user_id=".currentUser()->getId()." AND special=0 ORDER BY sort ASC");
		while($data = mysqli_fetch_assoc($result)) {
			$ret[$data['id']] = new PMCategoryData($data);
		}
		uasort($ret,'comparePMCategories');
		return $ret;
	}

	static function getNavletCategories() {
		$ret = array();
		$ret[PM_SPECIAL_MAIN] = self::getOrGenSpecialCategory(PM_SPECIAL_MAIN,currentUser()->getId());
		$ret[PM_SPECIAL_SHIP] = self::getOrGenSpecialCategory(PM_SPECIAL_SHIP,currentUser()->getId());
		$ret[PM_SPECIAL_COLONY] = self::getOrGenSpecialCategory(PM_SPECIAL_COLONY,currentUser()->getId());
		$ret[PM_SPECIAL_TRADE] = self::getOrGenSpecialCategory(PM_SPECIAL_TRADE,currentUser()->getId());
		return $ret;
	}

	static function getOrGenSpecialCategory($type,$userId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE user_id=".$userId." AND special=".$type." LIMIT 1",4);
		if ($result == 0) {
			$class = 'StdPMCat_'.$type;
			return new $class($userId);
		}
		return new PMCategoryData($result);
	}

	static function getById($catId) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".intval($catId)." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new PMCategoryData($result);
	}

	/**
	 */
	static function getObjectsBy($sql='') { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' '.$sql);
		return self::_getList($result,'PMCategoryData');
	} # }}}

}

class StdPMCat_1 extends PMCategoryData {
	function __construct($userId) {
		parent::__construct(array());
		$this->setUserId($userId);
		$this->setDescription('Persönlich');
		$this->setSpecial(1);
		$this->setSort(1);
		$this->save();
	}
}

class StdPMCat_2 extends PMCategoryData {
	function __construct($userId) {
		parent::__construct(array());
		$this->setUserId($userId);
		$this->setDescription('Schiffe');
		$this->setSpecial(2);
		$this->setSort(2);
		$this->save();
	}
}

class StdPMCat_3 extends PMCategoryData {
	function __construct($userId) {
		parent::__construct(array());
		$this->setUserId($userId);
		$this->setDescription('Kolonien');
		$this->setSpecial(3);
		$this->setSort(3);
		$this->save();
	}
}

class StdPMCat_4 extends PMCategoryData {
	function __construct($userId) {
		parent::__construct(array());
		$this->setUserId($userId);
		$this->setDescription('Handel');
		$this->setSpecial(4);
		$this->setSort(4);
		$this->save();
	}
}

class StdPMCat_5 extends PMCategoryData {
	function __construct($userId) {
		parent::__construct(array());
		$this->setUserId($userId);
		$this->setDescription('Postausgang');
		$this->setSpecial(5);
		$this->setSort(5);
		$this->save();
	}
}
?>
