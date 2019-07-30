<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id:$ */

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ColonyStorageGoodWrapper { #{{{

	private $storage = NULL;

	/**
	 */
	function __construct(&$storage) { #{{{
		$this->storage = $storage;
	} # }}}

	/**
	 */
	public function __get($goodId) { #{{{
		return new ColonyStorageGoodCountWrapper($this->storage,$goodId);
	} # }}}

	/**
	 */
	public function __call($name,$arg) { #{{{
		return $this->__get($name);
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ColonyStorageGoodCountWrapper { #{{{

	const CHECK_ONLY = 'x';

	private $storage = NULL;
	private $goodId = NULL;

	/**
	 */
	function __construct(&$storage,$goodId) { #{{{
		$this->storage = $storage;
		$this->goodId = $goodId;
	} # }}}

	/**
	 */
	public function __get($count) { #{{{
		if (!isset($this->storage[$this->goodId])) {
			return FALSE;
		}
		if ($count == self::CHECK_ONLY) {
			return TRUE;
		}
		if ($this->storage[$this->goodId]->getCount() < $count) {
			return FALSE;
		}
		return TRUE;
	} # }}}

	/**
	 */
	public function __call($name,$arg) { #{{{
		return $this->__get($name);
	} # }}}

} #}}}

?>
