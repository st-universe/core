<?php

namespace Stu\Lib\ModuleScreen;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipRumpInterface;

class ModuleScreenTab
{
    private $moduleType;
    private $buildplan;
    private $colony;
    private $rump;

    public function __construct(
        int $moduleType,
        ColonyInterface $colony,
        ShipRumpInterface $rump,
        ?ShipBuildplanInterface $buildplan = null
    ) {
        $this->moduleType = $moduleType;
        $this->buildplan = $buildplan;
        $this->colony = $colony;
        $this->rump = $rump;
    }

    /**
     */
    public function getModuleType()
    {
        return $this->moduleType;
    }

    /**
     */
    public function getColony()
    {
        return $this->colony;
    }

    /**
     */
    public function getRump()
    {
        return $this->rump;
    }

    /**
     */
    public function getTabTitle()
    {
        return ModuleTypeDescriptionMapper::getDescription($this->getModuleType());
    }

    /**
     */
    public function isMandatory()
    {
        if ($this->getModuleType() === ShipModuleTypeEnum::MODULE_TYPE_SPECIAL) {
            return false;
        }
        return $this->getRump()->getModuleLevels()->{'getModuleMandatory' . $this->getModuleType()}() > 0;
    }

    /**
     */
    public function isSpecial()
    {
        return $this->getModuleType() === ShipModuleTypeEnum::MODULE_TYPE_SPECIAL;
    }


    /**
     */
    public function getBuildplan()
    {
        return $this->buildplan;
    }

    /**
     */
    public function hasBuildplan()
    {
        return $this->getBuildplan() != false;
    }

    /**
     */
    public function hasSelectedModule()
    {
        return $this->getSelectedModule() != false;
    }

    /**
     */
    public function getSelectedModule()
    {
        if (!$this->getBuildplan()) {
            return false;
        }
        if (!$this->getBuildplan()->getModulesByType($this->getModuleType())) {
            return false;
        }
        return $this->getBuildplan()->getModulesByType($this->getModuleType());
    }

    /**
     */
    public function getCssClass()
    {
        $class = 'module_select_base';
        if ($this->isMandatory()) {
            if (!$this->hasSelectedModule()) {
                $class .= ' module_select_base_mandatory';
            } else {
                $mod = current($this->getBuildplan()->getModulesByType($this->getModuleType()));
                $commodityId = $mod->getModule()->getCommodityId();

                $stor = $this->getColony()->getStorage()[$commodityId] ?? null;
                if ($stor === null) {
                    $class .= ' module_select_base_mandatory';
                }
            }
        }
        return $class;
    }
}
