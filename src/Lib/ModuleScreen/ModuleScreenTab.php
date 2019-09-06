<?php


namespace Stu\Lib\ModuleScreen;

use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleScreenTab
{ #{{{

	private $moduleType = null;
	private $buildplan = null;
	private $colony = null;
	private $rump = null;

	/**
	 */
	function __construct($moduleType, $colony, $rump, $buildplan = false)
	{ #{{{
		$this->moduleType = $moduleType;
		$this->buildplan = $buildplan;
		$this->colony = $colony;
		$this->rump = $rump;
	} # }}}

	/**
	 */
	public function getModuleType()
	{ #{{{
		return $this->moduleType;
	} # }}}

	/**
	 */
	public function getColony()
	{ #{{{
		return $this->colony;
	} # }}}

	/**
	 */
	public function getRump()
	{ #{{{
		return $this->rump;
	} # }}}

	/**
	 */
	public function getTabTitle()
	{ #{{{
		return ModuleTypeDescriptionMapper::getDescription($this->getModuleType());
	} # }}}

	/**
	 */
	public function isMandatory()
	{ #{{{
		if ($this->getModuleType() === MODULE_TYPE_SPECIAL) {
			return false;
		}
		return $this->getRump()->getModuleLevels()->{'getModuleMandatory' . $this->getModuleType()}() > 0;
	} # }}}

	/**
	 */
	public function getBuildplan()
	{ #{{{
		return $this->buildplan;
	} # }}}

	/**
	 */
	public function hasBuildplan()
	{ #{{{
		return $this->getBuildplan() != false;
	} # }}}

	/**
	 */
	public function hasSelectedModule()
	{ #{{{
		return $this->getSelectedModule() != false;
	} # }}}

	/**
	 */
	public function getSelectedModule()
	{ #{{{
		if (!$this->getBuildplan()) {
			return false;
		}
		if (!$this->getBuildplan()->getModulesByType($this->getModuleType())) {
			return false;
		}
		return $this->getBuildplan()->getModulesByType($this->getModuleType());
	} # }}}

	/**
	 */
	public function getCssClass()
	{ #{{{
		$class = 'module_select_base';
		if ($this->isMandatory()) {
			if (!$this->hasSelectedModule()) {
				$class .= ' module_select_base_mandatory';
			} else {
				$mod = current($this->getBuildplan()->getModulesByType($this->getModuleType()));
				$goodId = (string) $mod->getModule()->getGoodId();
				$i = '1';
				if (!$this->getColony()->hasStorage()->$goodId()->$i()) {
					$class .= ' module_select_base_mandatory';
				}
			}
		}
		return $class;
	} # }}}

} #}}}

