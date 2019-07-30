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
class ModuleSelectWrapper { #{{{

	private $buildplan = NULL;

	/**
	 */
	function __construct($buildplan) { #{{{
		$this->buildplan = $buildplan;
	} # }}}

	/**
	 */
	public function __get($type) { #{{{
		if (!$this->buildplan) {
			return FALSE;
		}
		$modules = $this->buildplan->getModulesByType($type);
		if (!$modules) {
			return FALSE;
		}
		return current($modules);
	} # }}}

} #}}}


?>
