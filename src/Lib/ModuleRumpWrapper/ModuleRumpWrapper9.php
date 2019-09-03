<?php

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;


class ModuleRumpWrapper9 extends \Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperBase
{ #{{{

	private $modules = null;

	/**
	 */
	function __construct($rump, $modules)
	{ #{{{
		$this->rump = $rump;
		$this->modules = $modules;
	} # }}}


	/**
	 */
	public function getCallBacks()
	{ #{{{
		$ret = array();
		foreach ($this->modules as $key => $module) {
			if ($module->getModule()->hasSpecial(ModuleSpecialAbilityEnum::MODULE_SPECIAL_CLOAK)) {
				$ret['setCloakAble'] = 1;
			}
		}
		return $ret;
	} # }}}

} #}}}
