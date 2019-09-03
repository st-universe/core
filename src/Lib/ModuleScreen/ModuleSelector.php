<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Modules;
use RumpModuleLevel;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use Stu\Module\Tal\TalPageInterface;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleSelector
{ #{{{

    private $moduleType = null;
    private $rump = null;
    private $userId = 0;
    private $macro = 'html/modulescreen.xhtml/moduleselector';
    private $templateFile = 'html/ajaxempty.xhtml';
    private $template = null;
    private $colony = null;
    private $buildplan = null;

    /**
     */
    function __construct($moduleType, $colony, $rump, $userId, $buildplan = false)
    { #{{{
        $this->moduleType = $moduleType;
        $this->rump = $rump;
        $this->userId = $userId;
        $this->colony = $colony;
        $this->buildplan = $buildplan;
    } # }}}

    /**
     */
    public function allowMultiple()
    { #{{{
        return false;
    } # }}}

    /**
     */
    private function getTemplate()
    { #{{{
        if ($this->template === null) {
            // @todo refactor
            global $container;

            $this->template = $container->get(TalPageInterface::class);
            $this->template->setTemplate($this->templateFile);
            $this->template->setVar('THIS', $this);
        }
        return $this->template;
    } # }}}

    /**
     */
    public function getMacro(): string
    { #{{{
        return $this->macro;
    } # }}}

    /**
     */
    public function render()
    { #{{{
        return $this->getTemplate()->parse(true);
    } # }}}

    /**
     */
    public function getModuleType()
    { #{{{
        return $this->moduleType;
    } # }}}

    /**
     */
    public function allowEmptySlot()
    { #{{{
        return $this->getRump()->getModuleLevels()->{'getModuleMandatory' . $this->getModuleType()}() == 0;
    } # }}}

    /**
     */
    public function getModuleDescription()
    { #{{{
        return ModuleTypeDescriptionMapper::getDescription($this->getModuleType());
    } # }}}

    /**
     */
    public function getUserId()
    { #{{{
        return $this->userId;
    } # }}}

    /**
     */
    public function getRump()
    { #{{{
        return $this->rump;
    } # }}}

    private $modules = null;

    /**
     */
    public function getAvailableModules()
    { #{{{
        if ($this->modules === null) {
            if ($this->getModuleType() == MODULE_TYPE_SPECIAL) {
                $special_query = ' AND id IN (SELECT module_id FROM stu_modules_specials WHERE special_id IN (SELECT module_special_id FROM stu_rumps_module_special WHERE rump_id=' . $this->getRump()->getId() . '))';
                $modules = Modules::getBy('type=' . $this->getModuleType() . ' AND rumps_role_id=
					(SELECT CASE WHEN (SELECT count(id) FROM stu_modules where type=' . $this->getModuleType() . ' AND rumps_role_id=' . $this->getRump()->getRoleId() . ')=0 THEN 0 ELSE ' . $this->getRump()->getRoleId() . ' END)
					AND (viewable=1 OR goods_id IN (SELECT goods_id FROM stu_colonies_storage WHERE colonies_id=' . $this->getColony()->getId() . '))
					' . $special_query);
            } else {
                $mod_level = RumpModuleLevel::getByRump($this->getRump()->getId());
                $min_level = $mod_level->{'getModuleLevel' . $this->getModuleType() . 'Min'}();
                $max_level = $mod_level->{'getModuleLevel' . $this->getModuleType() . 'Max'}();
                $modules = Modules::getBy('type=' . $this->getModuleType() . ' AND rumps_role_id=
					(SELECT CASE WHEN (SELECT count(id) FROM stu_modules where type=' . $this->getModuleType() . ' AND rumps_role_id=' . $this->getRump()->getRoleId() . ')=0 THEN 0 ELSE ' . $this->getRump()->getRoleId() . ' END)
					AND level IN (' . join(",", range($min_level, $max_level)) . ')
					AND (viewable=1 OR goods_id IN (SELECT goods_id FROM stu_colonies_storage WHERE colonies_id=' . $this->getColony()->getId() . '))');
            }
            foreach ($modules as $key => $obj) {
                $this->modules[$obj->getId()] = new \Stu\Lib\ModuleScreen\ModuleSelectorWrapper($obj,
                    $this->getBuildplan());
            }
        }
        return $this->modules;
    } # }}}

    /**
     */
    public function hasModuleSelected()
    { #{{{
        return new ModuleSelectWrapper($this->getBuildplan());
    } # }}}

    /**
     */
    public function getColony()
    { #{{{
        return $this->colony;
    } # }}}

    /**
     */
    public function getBuildplan()
    { #{{{
        return $this->buildplan;
    } # }}}

}