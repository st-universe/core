<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id:$ */

namespace Stu\Lib\ModuleScreen;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleSelectWrapper
{ #{{{

	private $buildplan = null;

	/**
	 */
	function __construct($buildplan)
	{ #{{{
		$this->buildplan = $buildplan;
	} # }}}

	/**
	 */
	public function __get($type)
	{ #{{{
		if (!$this->buildplan) {
			return false;
		}
		$modules = $this->buildplan->getModulesByType($type);
		if (!$modules) {
			return false;
		}
		return current($modules);
	} # }}}

} #}}}


?>
