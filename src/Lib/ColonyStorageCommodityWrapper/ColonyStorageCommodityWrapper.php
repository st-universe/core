<?php

namespace Stu\Lib\ColonyStorageCommodityWrapper;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ColonyStorageCommodityWrapper
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
	public function __get($commodityId)
	{ #{{{
		return new ColonyStorageCommodityCountWrapper($this->storage, $commodityId);
	} # }}}

	/**
	 */
	public function __call($name, $arg)
	{ #{{{
		return $this->__get($name);
	} # }}}

} #}}}
