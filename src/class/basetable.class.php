<?php
class BaseTable {

	private $updateFields = array();

	/**
	 */
	function __construct(&$data=array()) { #{{{
		$this->data = $data;
	} # }}}

	protected function addUpdateField($field,$callback) {
		$this->updateFields[$field] = $callback;
	}

	private function getUpdateFields() {
		return $this->updateFields;
	}
	
	public function save() {
		if (!$this->getId() || $this->getId() == 0) {
			$this->insertToDb();
		} else {	
			$this->updateToDb();
		}
	}

	private function updateToDb() {
		if (count($this->getUpdateFields()) == 0) {
			return;
		}
		$str = array();
		foreach($this->getUpdateFields() as $key => $value) {
			$str[] = $key."='".dbSafe($this->$value())."'";
		}
		DB()->query("UPDATE ".$this->getTable()." SET ".join(",",$str)." WHERE id=".($this->getCachedId() ? $this->getCachedId() : $this->getId())." LIMIT 1");
		$this->updateFields = array();
		if ($this->getId() != $this->getCachedId()) {
			$this->setCachedId($this->getId());
		}
	}

	protected function insertToDb() {
		if (count($this->getUpdateFields()) == 0) {
			return;
		}
		$vars = array();
		$vals = array();
		foreach($this->getUpdateFields() as $key => $value) {
			$vars[] = $key;
			$vals[] = "'".dbSafe($this->$value())."'";
		}
		$id = DB()->query("INSERT INTO ".$this->getTable()." (".join(",",$vars).") VALUES (".join(",",$vals).")",5);
		$this->updateFields = array();
		$this->updateId($id);
	}

	/**
	 */
	private function updateId($id) { #{{{
		$this->setId($id);
		$this->setCachedId($id);
	} # }}}

	private $cached_id = 0;

	/**
	 */
	private function getCachedId() { #{{{
		return $this->cached_id;
	} # }}}
	
	/**
	 */
	private function setCachedId($id) { #{{{
		$this->cached_id = $id;
	} # }}}

	protected $data = array();

	public function isNew() {
		return !$this->getId();
	}

	public function setNew() {
		$this->data['id'] = 0;
	}

	public function deleteFromDatabase() {
		DB()->query("DELETE FROM ".$this->getTable()." WHERE id=".$this->getId()." LIMIT 1");
	}

	protected function getTable() {
		return $this->tablename;
	}

	protected function setFieldValue($field,$value,$callback) {
		$this->data[$field] = $value;
		$this->addUpdateField($field,$callback);
	}

	public function getId() {
		return (int) $this->data['id'];
	}

	protected function setId($value) {
		$this->data['id'] = $value;
	}

	/**
	 */
	public function forceId($value) { #{{{
		if ($this->getId() && !$this->getCachedId()) {
			$this->setCachedId($this->getId());
		}
		$this->setFieldValue('id',$value,'getId');
	} # }}}

	protected function _getList($result,$creator,$index="id",$cachevalue=FALSE) {
		$ret = array();
		while ($data = mysqli_fetch_assoc($result)) {
			if ($cachevalue) {
				$ret[$data[$index]] = &ResourceCache()->getObject($cachevalue,$data['id']);
				continue;
			}
			$ret[$data[$index]] = new $creator($data);
		}
		return $ret;
	}

	/**
	 */
	protected function _getListAsArrayObject($result,$creator,$index="id",$cache_value=FALSE) { #{{{
		$ret = new ArrayObject;
		while ($data = mysqli_fetch_assoc($result)) {
			if ($cache_value) {
				$ret->offsetSet($data[$index],ResourceCache()->getObject($cache_value,$data['id']));
				continue;
			}
			$ret->offsetSet($data[$index],new $creator($data));
		}
		return $ret;
	} # }}}

	protected function _getBy($result,&$id,$creator) {
		if ($result == 0) {
			new ObjectNotFoundException($id,$creator);
		}
		return new $creator($result);
	}

	/**
	 */
	protected function getCount($table,$qry) { #{{{
		return DB()->query("SELECT COUNT(*) FROM ".$table." WHERE ".$qry,1);
	} # }}}

}
?>
