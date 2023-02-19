<?php

namespace Stu\Lib\ModuleScreen;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;

class ModuleScreenTab
{
    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    private int $moduleType;

    private ?ShipBuildplanInterface $buildplan;

    private ColonyInterface $colony;

    private ShipRumpInterface $rump;

    public function __construct(
        ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        int $moduleType,
        ColonyInterface $colony,
        ShipRumpInterface $rump,
        ?ShipBuildplanInterface $buildplan = null
    ) {
        $this->shipRumpModuleLevelRepository = $shipRumpModuleLevelRepository;
        $this->moduleType = $moduleType;
        $this->buildplan = $buildplan;
        $this->colony = $colony;
        $this->rump = $rump;
    }

    public function getModuleType(): int
    {
        return $this->moduleType;
    }

    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    public function getRump(): ShipRumpInterface
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
        $moduleLevels = $this->shipRumpModuleLevelRepository->getByShipRump($this->rump->getId());

        return $moduleLevels->{'getModuleMandatory' . $this->getModuleType()}() > 0;
    }

    public function isSpecial(): bool
    {
        return $this->getModuleType() === ShipModuleTypeEnum::MODULE_TYPE_SPECIAL;
    }

    public function getBuildplan(): ?ShipBuildplanInterface
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
     * @return false|array<int, BuildplanModuleInterface>
     */
    public function getSelectedModule()
    {
        if ($this->buildplan === null) {
            return false;
        }
        if (!$this->buildplan->getModulesByType($this->getModuleType())) {
            return false;
        }
        return $this->buildplan->getModulesByType($this->getModuleType());
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
