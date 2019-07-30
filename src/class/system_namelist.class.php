<?php



/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class SystemNameListData extends BaseTable { #{{{

	const tablename = 'stu_system_namelist';
	protected $tablename = 'stu_system_namelist';
	
	/**
	 */
	function __construct(&$data=array()) { #{{{
		$this->data = $data;
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

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class SystemNameList extends SystemNameListData { #{{{

	/**
	 */
	function __construct($id=0) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	} # }}}

	/**
	 */
	static function findObject($sql) { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' '.$sql.' LIMIT 1',4);
		if ($result == 0) {
			return FALSE;
		}
		return new SystemNameListData($result);
	} # }}}

} #}}}


?>
