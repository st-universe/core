<?php

namespace Stu\Lib\ColonyStorageGoodWrapper;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ColonyStorageGoodWrapper
{ #{{{

	private $storage = null;

	/**
	 */
	function __construct(&$storage)
	{ #{{{
		$this->storage = $storage;
	} # }}}

	/**
	 */
	public function __get($goodId)
	{ #{{{
		return new \Stu\Lib\ColonyStorageGoodWrapper\ColonyStorageGoodCountWrapper($this->storage, $goodId);
	} # }}}

	/**
	 */
	public function __call($name, $arg)
	{ #{{{
		return $this->__get($name);
	} # }}}

} #}}}
