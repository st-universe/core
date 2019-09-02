<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id:$ */

use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapperBase { #{{{

	private $module = NULL;
	protected $rump = NULL;

	/**
	 */
	function __construct($rump,$module) { #{{{
		$this->module = $module;
		$this->rump = $rump;
	} # }}}

	/**
	 */
	protected function getRump() { #{{{
		return $this->rump;
	} # }}}

	/**
	 */
	public function getModule() { #{{{
		return $this->module;
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapper1 extends ModuleRumpWrapperBase { #{{{

	/**
	 */
	public function getValue() { #{{{
		return calculateModuleValue($this->getRump(),current($this->getModule())->getModule(),'getBaseHull');
	} # }}}


	/**
	 */
	public function getCallBacks() { #{{{
		$callbacks = array('setMaxHuelle' => $this->getValue(),
				   'setHuell' => $this->getValue());
		return $callbacks;
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapper2 extends ModuleRumpWrapperBase { #{{{

	/**
	 */
	public function getValue() { #{{{
		return calculateModuleValue($this->getRump(),current($this->getModule())->getModule(),'getBaseShield');
	} # }}}


	/**
	 */
	public function getCallBacks() { #{{{
		$callbacks = array('setMaxShield' => $this->getValue());
		return $callbacks;
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapper3 extends ModuleRumpWrapperBase { #{{{

	/**
	 */
	public function getValue() { #{{{
		return calculateModuleValue($this->getRump(),current($this->getModule())->getModule(),'getBaseEps');
	} # }}}


	/**
	 */
	public function getCallBacks() { #{{{
		$callbacks = array('setMaxEps' => $this->getValue());
		return $callbacks;
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapper4 extends ModuleRumpWrapperBase { #{{{

	/**
	 */
	public function getValue() { #{{{
		return calculateEvadeChance($this->getRump(),current($this->getModule())->getModule());
	} # }}}


	/**
	 */
	public function getCallBacks() { #{{{
		$callbacks = array('setEvadeChance' => $this->getValue());
		return $callbacks;
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapper5 extends ModuleRumpWrapperBase { #{{{

	/**
	 */
	public function getValue() { #{{{
		return calculateModuleValue($this->getRump(),current($this->getModule())->getModule(),'getBaseReactor');
	} # }}}


	/**
	 */
	public function getCallBacks() { #{{{
		$callbacks = array('setReactorOutput' => $this->getValue());
		return $callbacks;
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapper6 extends ModuleRumpWrapperBase { #{{{

	/**
	 */
	public function getValue() { #{{{
		return calculateModuleValue($this->getRump(),current($this->getModule())->getModule(),'getHitChance');
	} # }}}


	/**
	 */
	public function getCallBacks() { #{{{
		$callbacks = array('setHitChance' => $this->getValue());
		return $callbacks;
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapper7 extends ModuleRumpWrapperBase { #{{{

	/**
	 */
	public function getValue() { #{{{
		return calculateModuleValue($this->getRump(),current($this->getModule())->getModule(),'getBaseDamage');
	} # }}}


	/**
	 */
	public function getCallBacks() { #{{{
		$callbacks = array('setBaseDamage' => $this->getValue());
		return $callbacks;
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapper8 extends ModuleRumpWrapperBase { #{{{

	/**
	 */
	public function getValue() { #{{{
		return 0;
	} # }}}


	/**
	 */
	public function getCallBacks() { #{{{
		return array();
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleRumpWrapper9 extends ModuleRumpWrapperBase { #{{{

	private $modules = NULL;

	/**
	 */
	function __construct($rump,$modules) { #{{{
		$this->rump = $rump;
		$this->modules = $modules;
	} # }}}


	/**
	 */
	public function getCallBacks() { #{{{
		$ret = array();
		foreach ($this->modules as $key => $module) {
			if ($module->getModule()->hasSpecial(ModuleSpecialAbilityEnum::MODULE_SPECIAL_CLOAK)) {
				$ret['setCloakAble'] = 1;
			}
		}
		return $ret;
	} # }}}
	
} #}}}
?>
