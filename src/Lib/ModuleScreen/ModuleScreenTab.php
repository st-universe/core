<?php

namespace Stu\Lib\ModuleScreen;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipRumpInterface;

class ModuleScreenTab
{
    /** @var int */
    private $moduleType;

    /** @var ShipBuildplanInterface|null  */
    private $buildplan;

    /** @var ColonyInterface */
    private $colony;

    /** @var ShipRumpInterface */
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
     * @return int
     */
    public function getModuleType()
    {
        return $this->moduleType;
    }

    /**
     * @return ColonyInterface
     */
    public function getColony()
    {
        return $this->colony;
    }

    /**
     * @return ShipRumpInterface
     */
    public function getRump()
    {
        return $this->rump;
    }

    public function getTabTitle(): string
    {
        return ModuleTypeDescriptionMapper::getDescription($this->getModuleType());
    }

    public function isMandatory(): bool
    {
        if ($this->getModuleType() === ShipModuleTypeEnum::MODULE_TYPE_SPECIAL) {
            return false;
        }
        return $this->getRump()->getModuleLevels()->{'getModuleMandatory' . $this->getModuleType()}() > 0;
    }

    public function isSpecial(): bool
    {
        return $this->getModuleType() === ShipModuleTypeEnum::MODULE_TYPE_SPECIAL;
    }


    /**
     * @return null|ShipBuildplanInterface
     */
    public function getBuildplan()
    {
        return $this->buildplan;
    }

    public function hasBuildplan(): bool
    {
        return $this->getBuildplan() != false;
    }

    public function hasSelectedModule(): bool
    {
        return $this->getSelectedModule() != false;
    }

    /**
     * @return false|array<BuildplanModuleInterface>
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

    public function getCssClass(): string
    {
        $class = 'module_select_base';
        if ($this->isMandatory()) {
            if (!$this->hasSelectedModule()) {
                $class .= ' module_select_base_mandatory';
            } else {
                /** @var BuildplanModuleInterface $mod */
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
