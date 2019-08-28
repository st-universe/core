<?php

use Stu\Module\Tal\TalPage;
use Stu\Module\Tal\TalPageInterface;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleSelectorSpecial extends ModuleSelector { #{{{

	/**
	 */
	public function allowMultiple() { #{{{
		return TRUE;
	} # }}}

} #}}}


/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleSelector { #{{{

	private $moduleType = NULL;
	private $rump = NULL;
	private $userId = 0;
	private $macro = 'html/modulescreen.xhtml/moduleselector';
	private $templateFile = 'html/ajaxempty.xhtml';
	private $template = NULL;
	private $colony = NULL;
	private $buildplan = NULL;

	/**
	 */
	function __construct($moduleType,$colony,$rump,$userId,$buildplan=FALSE) { #{{{
		$this->moduleType = $moduleType;
		$this->rump = $rump;
		$this->userId = $userId;
		$this->colony = $colony;
		$this->buildplan = $buildplan;
	} # }}}

	/**
	 */
	public function allowMultiple() { #{{{
		return FALSE;
	} # }}}

	/**
	 */
	private function getTemplate() { #{{{
		if ($this->template === NULL) {
			// @todo refactor
			global $container;

			$this->template = $container->get(TalPageInterface::class);
			$this->template->setTemplate($this->templateFile);
			$this->template->setVar('THIS',$this);
			$this->template->setVar('GFX', GFX_PATH);
		}
		return $this->template;
	} # }}}

	/**
	 */
	public function getMacro(): string { #{{{
		return $this->macro;
	} # }}}

	/**
	 */
	public function render() { #{{{
		return $this->getTemplate()->parse(TRUE);
	} # }}}

	/**
	 */
	public function getModuleType() { #{{{
		return $this->moduleType;
	} # }}}

	/**
	 */
	public function allowEmptySlot() { #{{{
		return $this->getRump()->getModuleLevels()->{'getModuleMandatory'.$this->getModuleType()}() == 0;
	} # }}}

	/**
	 */
	public function getModuleDescription() { #{{{
		return ModuleType::getDescription($this->getModuleType());
	} # }}}

	/**
	 */
	public function getUserId() { #{{{
		return $this->userId;
	} # }}}

	/**
	 */
	public function getRump() { #{{{
		return $this->rump;
	} # }}}

	private $modules = NULL;

	/**
	 */
	public function getAvailableModules() { #{{{
		if ($this->modules === NULL) {
			if ($this->getModuleType() == MODULE_TYPE_SPECIAL) {
				$special_query = ' AND id IN (SELECT module_id FROM stu_modules_specials WHERE special_id IN (SELECT module_special_id FROM stu_rumps_module_special WHERE rump_id='.$this->getRump()->getId().'))';
				$modules = Modules::getBy('type='.$this->getModuleType().' AND rumps_role_id=
					(SELECT CASE WHEN (SELECT count(id) FROM stu_modules where type='.$this->getModuleType().' AND rumps_role_id='.$this->getRump()->getRoleId().')=0 THEN 0 ELSE '.$this->getRump()->getRoleId().' END)
					AND (viewable=1 OR goods_id IN (SELECT goods_id FROM stu_colonies_storage WHERE colonies_id='.$this->getColony()->getId().'))
					'.$special_query);
			} else {
				$mod_level = RumpModuleLevel::getByRump($this->getRump()->getId());
				$min_level = $mod_level->{'getModuleLevel'.$this->getModuleType().'Min'}();
				$max_level = $mod_level->{'getModuleLevel'.$this->getModuleType().'Max'}();
				$modules = Modules::getBy('type='.$this->getModuleType().' AND rumps_role_id=
					(SELECT CASE WHEN (SELECT count(id) FROM stu_modules where type='.$this->getModuleType().' AND rumps_role_id='.$this->getRump()->getRoleId().')=0 THEN 0 ELSE '.$this->getRump()->getRoleId().' END)
					AND level IN ('.join(",",range($min_level,$max_level)).')
					AND (viewable=1 OR goods_id IN (SELECT goods_id FROM stu_colonies_storage WHERE colonies_id='.$this->getColony()->getId().'))');
			}
			foreach ($modules as $key => $obj) {
				$this->modules[$obj->getId()] = new ModuleSelectorWrapper($obj,$this->getBuildplan());
			}
		}
		return $this->modules;
	} # }}}

	/**
	 */
	public function hasModuleSelected() { #{{{
		return new ModuleSelectWrapper($this->getBuildplan());
	} # }}}

	/**
	 */
	public function getColony() { #{{{
		return $this->colony;
	} # }}}

	/**
	 */
	public function getBuildplan() { #{{{
		return $this->buildplan;
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleSelectorWrapper { #{{{

	private $module = NULL;
	private $buildplan = NULL;

	/**
	 */
	function __construct($module,$buildplan) { #{{{
		$this->module = $module;
		$this->buildplan = $buildplan;
	} # }}}

	/**
	 */
	public function isChoosen() { #{{{
		$request = request::postArray('mod_'.$this->getModule()->getType());
		if ($this->getBuildplan()) {
			if (array_key_exists($this->getModule()->getId(),$this->getBuildplan()->getModulesByType($this->getModule()->getType()))) {
				return TRUE;
			}
		}
		if (!is_array($request) || !array_key_exists($this->getModule()->getId(),$request)) {
			return FALSE;
		}
		return TRUE;
	} # }}}

	/**
	 */
	public function getBuildplan() { #{{{
		return $this->buildplan;
	} # }}}

	/**
	 */
	public function getModule() { #{{{
		return $this->module;
	} # }}}

} #}}}

?>
