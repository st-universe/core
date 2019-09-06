<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;

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
        // @todo refactor
        global $container;
        if ($this->modules === null) {
            if ($this->getModuleType() == MODULE_TYPE_SPECIAL) {
                $modules = $container->get(ModuleRepositoryInterface::class)->getBySpecialTypeAndRump(
                    (int) $this->getColony()->getId(),
                    (int) $this->getModuleType(),
                    (int) $this->getRump()->getId(),
                    (int) $this->getRump()->getRoleId()
                );
            } else {
                $mod_level = $container->get(ShipRumpModuleLevelRepositoryInterface::class)->getByShipRump(
                    (int) $this->getRump()->getId()
                );

                $min_level = $mod_level->{'getModuleLevel' . $this->getModuleType() . 'Min'}();
                $max_level = $mod_level->{'getModuleLevel' . $this->getModuleType() . 'Max'}();

                $modules = $container->get(ModuleRepositoryInterface::class)->getByTypeAndLevel(
                    (int) $this->getColony()->getId(),
                    (int) $this->getModuleType(),
                    (int) $this->getRump()->getRoleId(),
                    range($min_level, $max_level)
                );
            }
            foreach ($modules as $obj) {
                $this->modules[$obj->getId()] = new ModuleSelectorWrapper($obj, $this->getBuildplan());
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