<?php

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class SystemTypeData extends BaseTable { #{{{

	const tablename = 'stu_system_types';
	protected $tablename = 'stu_system_types';

	/**
	 */
	function __construct(&$data=array()) { #{{{
		$this->data = $data;
	} # }}}

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

	/**
	 */
	public function setDatabaseId($value) { # {{{
		$this->setFieldValue('database_id',$value,'getDatabaseId');
	} # }}}

	/**
	 */
	public function getDatabaseId() { # {{{
		return $this->data['database_id'];
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class SystemType extends SystemTypeData { #{{{

	function __construct($id=0) { # {{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	} # }}}

	/**
	 */
	static function getObjectsBy($sql="") { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'SystemTypeData');
	} # }}}

} #}}}

?>
