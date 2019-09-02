<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id:$ */

use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleScreenTab { #{{{

	private $moduleType = NULL;
	private $buildplan = NULL;
	private $colony = NULL;
	private $rump = NULL;

	/**
	 */
	function __construct($moduleType,$colony,$rump,$buildplan=FALSE) { #{{{
		$this->moduleType = $moduleType;
		$this->buildplan = $buildplan;
		$this->colony = $colony;
		$this->rump = $rump;
	} # }}}

	/**
	 */
	public function getModuleType() { #{{{
		return $this->moduleType;
	} # }}}

	/**
	 */
	public function getColony() { #{{{
		return $this->colony;
	} # }}}

	/**
	 */
	public function getRump() { #{{{
		return $this->rump;
	} # }}}

	/**
	 */
	public function getTabTitle() { #{{{
		return ModuleTypeDescriptionMapper::getDescription($this->getModuleType());
	} # }}}

	/**
	 */
	public function isMandatory() { #{{{
		if ($this->getModuleType() === MODULE_TYPE_SPECIAL) {
			return FALSE;
		}
		return $this->getRump()->getModuleLevels()->{'getModuleMandatory'.$this->getModuleType()}() > 0;
	} # }}}

	/**
	 */
	public function getBuildplan() { #{{{
		return $this->buildplan;
	} # }}}

	/**
	 */
	public function hasBuildplan() { #{{{
		return $this->getBuildplan() != FALSE;
	} # }}}

	/**
	 */
	public function hasSelectedModule() { #{{{
		return $this->getSelectedModule() != FALSE;
	} # }}}

	/**
	 */
	public function getSelectedModule() { #{{{
		if (!$this->getBuildplan()) {
			return FALSE;
		}
		if (!$this->getBuildplan()->getModulesByType($this->getModuleType())) {
			return FALSE;
		}
		return $this->getBuildplan()->getModulesByType($this->getModuleType()); 
	} # }}}

	/**
	 */
	public function getCssClass() { #{{{
		$class = 'module_select_base';
		if ($this->isMandatory()) {
			if (!$this->hasSelectedModule()) {
				$class .= ' module_select_base_mandatory';
			} else {
				$mod = current($this->getBuildplan()->getModulesByType($this->getModuleType()));
				$goodId = $mod->getModule()->getGoodId();
				$i = '1';
				if (!$this->getColony()->hasStorage()->$goodId()->$i()) {
					$class .= ' module_select_base_mandatory';
				}
			}
		}
		return $class;
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleScreenTabWrapper { #{{{

	private $tabs = NULL;

	/**
	 */
	public function register($tab) { #{{{
		$this->tabs[$tab->getModuleType()] = $tab;
	} # }}}

	/**
	 */
	public function __get($moduleType) { #{{{
		return $this->tabs[$moduleType];
	} # }}}

} #}}}

?>
