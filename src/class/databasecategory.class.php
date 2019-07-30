<?php

class DatabaseCategoryData extends BaseTable {

	protected $tablename = 'stu_database_categories';
	const tablename = 'stu_database_categories';

	function __construct(&$data = array()) {
		$this->data = $data;
	}

	function setId($value) {
		$this->data['id'] = $value;
	}

	function getId() {
		return $this->data['id'];
	}

	function getDescription() {
		return decodeString($this->data['description']);
	}

	private $entries = NULL;

	function getEntries() {
		if ($this->entries === NULL) {
			$this->entries = DatabaseEntry::getByCategory($this->getId());
		}
		return $this->entries;
	}

	/**
	 */
	public function isCategoryStarSystems() { #{{{
		return $this->getId() == DATABASE_CATEGORY_STARSYSTEMS;
	} # }}}

	/**
	 */
	public function isCategoryTradePosts() { #{{{
		return $this->getId() == DATABASE_CATEGORY_TRADEPOSTS;
	} # }}}

	/**
	 */
	public function displayDefaultList() { #{{{
		return !$this->isCategoryStarSystems() && !$this->isCategoryTradePosts();
	} # }}}

	/**
	 */
	public function setPoints($value) { # {{{
		$this->setFieldValue('points',$value,'getPoints');
	} # }}}

	/**
	 */
	public function getPoints() { # {{{
		return $this->data['points'];
	} # }}}
	
}

class DatabaseCategory extends DatabaseCategoryData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".$this->getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	static function getCategoriesByType($type=0) {
		$result = DB()->query("SELECT * FROM ".parent::tablename." WHERE type=".intval($type)." ORDER BY sort");
		$ret = array();
		while($data = mysqli_fetch_assoc($result)) {
			$ret[] = new DatabaseCategoryData($data);
		}
		return $ret;
	}
}
?>
