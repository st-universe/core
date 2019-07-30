<?php
/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @access public
 */
class Base_Table { #{{{

	/**
	 */
	public function save() { #{{{
		if ($this->isNew()) {
			$this->insertToDb();
		} else {
			$this->updateToDb();
		}
	} # }}}

	/**
	 */
	private function updateToDb() { #{{{
		$str = array();
		foreach($this->_getData() as $key => $value) {
			$str[] = $key."='".dbSafe($value)."'";
		}
		DB()->query("UPDATE ".$this->getTableName()." SET ".join(",",$str)." WHERE id=".$this->getId()." LIMIT 1");
	} # }}}

	/**
	 */
	private function insertToDb() { #{{{
		$vars = array();
		$vals = array();
		foreach($this->_getData() as $key => $value) {
			$vars[] = $key;
			$vals[] = "'".dbSafe($value)."'";
		}
		$this->setId(DB()->query("INSERT INTO ".$this->getTableName()." (".join(",",$vars).") VALUES (".join(",",$vals).")",5));
	} # }}}

	/**
	 */
	public function isNew() { #{{{
		return !array_key_exists('id',$this->_getData()) || $this->getId() == 0;
	} # }}}

        /**
         */
        protected static function _getList($query,$creator) {
                if ($creator === FALSE) {
                        trigger_error("Creator is empty");
                }
                $result = DB()->query($query);
                $objs = array();
		while ($data = mysqli_fetch_assoc($result)) {
			$objs[$data['id']] = $creator->_create();
			$objs[$data['id']]->_setData($data);
		}
                DB()->freeResult($result);
                return $objs;
        }

	/**
	 */
	public function deleteFromDatabase() { #{{{
		if (!$this->isNew()) {
			DB()->query('DELETE FROM '.$this->getTableName().' WHERE id='.$this->getId());
		}
	} # }}}


} #}}}
?>
