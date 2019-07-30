<?php
class DatabaseEntryData extends BaseTable {

	protected $tablename = 'stu_database_entrys';
	const tablename = 'stu_database_entrys';
	
	function __construct(&$data = array()) {
		$this->data = $data;
	}

	/**
	 */
	public function setDescription($value) { # {{{
		$this->setFieldValue('description',$value,'getDescription');
	} # }}}

	/**
	 */
	public function getDescription() { # {{{
		return $this->data['description'];
	} # }}}

	function getCategoryId() {
		return $this->data['category_id'];
	}

	function setCategoryId($value) {
		$this->data['category_id'] = $value;
		$this->addUpdateField('category_id','getCategoryId');
	}

	private $category = NULL;

	/**
	 */
	function getCategory() { #{{{
		if ($this->category === NULL) {
			$this->category = new DatabaseCategory($this->getCategoryId());
		}
		return $this->category;
	} # }}}

	function getType() {
		return $this->data['type'];
	}

	function setType($value) {
		$this->data['type'] = $value;
		$this->addUpdateField('type','getType');
	}

	function getData() {
		return $this->data['data'];
	}

	function setData($value) {
		$this->data['data'] = $value;
		$this->addUpdateField('data','getData');
	}

	public function getDataFormatted() {
		return nl2br($this->getData());
	}

	function hasType() {
		return $this->getType() > 0;
	}

	private $typeobject = NULL;

	function getTypeObject() {
		if ($this->typeobject === NULL) {
			$this->typeobject = new DatabaseType($this->getType());
		}
		return $this->typeobject;
	}

	private $discovered = NULL;

	function isDiscoveredByCurrentUser() {
		if ($this->discovered === NULL) {
			$this->discovered = DatabaseUser::checkEntry($this->getId(),currentUser()->getId());
		}
		return $this->discovered;
	}

	private $userobj = NULL;

	function getDBUserObject() {
		if (!$this->isDiscoveredByCurrentUser()) {
			return;
		}
		if ($this->userobj === NULL) {
			$this->userobj = DatabaseUser::getBy($this->getId(),currentUser()->getId());
		}
		return $this->userobj;
	}

	public function getObjectId() {
		return $this->data['object_id'];
	}

	public function setObjectId($value) {
		$this->setFieldValue('object_id',$value,'getObjectId');
	}

	/**
	 */
	public function setSort($value) { # {{{
		$this->setFieldValue('sort',$value,'getSort');
	} # }}}

	/**
	 */
	public function getSort() { # {{{
		return $this->data['sort'];
	} # }}}

	private $object = NULL;

	/**
	 */
	public function getObject() { #{{{
		if ($this->object === NULL) {
			$this->object = FALSE;
			switch ($this->getCategoryId()) {
				case DATABASE_CATEGORY_STARSYSTEMS:
					$this->object = new StarSystem($this->getObjectId());
					break;
				case DATABASE_CATEGORY_TRADEPOSTS:
					$this->object = new Ship($this->getObjectId());
					break;
				case DATABASE_CATEGORY_SHIPRUMP:
					$this->object = new ShipRump($this->getObjectId());
					break;
			}
		}
		return $this->object;
	} # }}}

}

class DatabaseEntry extends DatabaseEntryData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".$this->getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getByCategory($categoryId=0) {
		$result = DB()->query("SELECT * FROM ".parent::tablename." WHERE category_id=".intval($categoryId)." ORDER BY sort");
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new DatabaseEntryData($data);
		}
		return $ret;
	}

	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}

}
?>
