<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\ShipRumpModuleLevelInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;

class ModuleSelector implements ModuleSelectorInterface
{

    private $moduleType;
    private $rump;
    private $userId;
    private $macro = 'html/modulescreen.xhtml/moduleselector';
    private $templateFile = 'html/ajaxempty.xhtml';
    private $colony;
    private $ship;
    private $buildplan;

    private ModuleRepositoryInterface $moduleRepository;

    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    private TalPageInterface $talPage;

    public function __construct(
        ModuleRepositoryInterface $moduleRepository,
        ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        TalPageInterface $talPage,
        $moduleType,
        ?ColonyInterface $colony,
        ?ShipInterface $ship,
        ShipRumpInterface $rump,
        int $userId,
        ?ShipBuildplanInterface $buildplan = null
    ) {
        $this->moduleType = $moduleType;
        $this->rump = $rump;
        $this->userId = $userId;
        $this->colony = $colony;
        $this->ship = $ship;
        $this->buildplan = $buildplan;
        $this->moduleRepository = $moduleRepository;
        $this->shipRumpModuleLevelRepository = $shipRumpModuleLevelRepository;
        $this->talPage = $talPage;
    }

    public function allowMultiple(): bool
    {
        return false;
    }

    public function getMacro(): string
    {
        return $this->macro;
    }

    public function render(): string
    {
        $this->talPage->setTemplate($this->templateFile);
        $this->talPage->setVar('THIS', $this);
        return $this->talPage->parse();
    }

    public function getModuleType(): int
    {
        return $this->moduleType;
    }

    public function allowEmptySlot(): bool
    {
        return $this->getModuleLevels()->{'getModuleMandatory' . $this->getModuleType()}() == ShipModuleTypeEnum::MODULE_OPTIONAL;
    }

    public function getModuleDescription(): string
    {
        return ModuleTypeDescriptionMapper::getDescription($this->getModuleType());
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRump(): ShipRumpInterface
    {
        return $this->rump;
    }

    private $modules;

    /**
     * @return ModuleSelectorWrapper[]
     */
    public function getAvailableModules(): array
    {
        if ($this->modules === null) {
            $this->modules = [];
            if ($this->getModuleType() == ShipModuleTypeEnum::MODULE_TYPE_SPECIAL) {
                if ($this->getColony() !== null) {
                    $modules = $this->moduleRepository->getBySpecialTypeColonyAndRump(
                        (int)$this->getColony()->getId(),
                        (int)$this->getModuleType(),
                        $this->getRump()->getId(),
                        $this->getRump()->getShipRumpRole()->getId()
                    );
                } else {
                    $modules = $this->moduleRepository->getBySpecialTypeShipAndRump(
                        (int)$this->ship->getId(),
                        (int)$this->getModuleType(),
                        $this->getRump()->getId(),
                        $this->getRump()->getShipRumpRole()->getId()
                    );
                }
            } else {
                $mod_level = $this->shipRumpModuleLevelRepository->getByShipRump(
                    $this->getRump()->getId()
                );

                $min_level = $mod_level->{'getModuleLevel' . $this->getModuleType() . 'Min'}();
                $max_level = $mod_level->{'getModuleLevel' . $this->getModuleType() . 'Max'}();

                $modules = $this->moduleRepository->getByTypeColonyAndLevel(
                    (int)$this->getColony()->getId(),
                    (int)$this->getModuleType(),
                    $this->getRump()->getShipRumpRole()->getId(),
                    range($min_level, $max_level)
                );
            }
            foreach ($modules as $obj) {
                $this->modules[$obj->getId()] = new ModuleSelectorWrapper($obj, $this->getBuildplan());
            }
        }
        return $this->modules;
    }

    public function hasModuleSelected(): ModuleSelectWrapper
    {
        return new ModuleSelectWrapper($this->buildplan);
    }

    public function getColony(): ?ColonyInterface
    {
        return $this->colony;
    }

    public function getBuildplan(): ?ShipBuildplanInterface
    {
        return $this->buildplan;
    }

    public function getModuleLevelClass(ShipRumpInterface $rump, ModuleSelectorWrapperInterface $module): string
    {
        $moduleLevels = $this->getModuleLevels();

        if ($moduleLevels->{'getModuleLevel' . $module->getModule()->getType()}() > $module->getModule()->getLevel()) {
            return 'module_positive';
        }
        if ($moduleLevels->{'getModuleLevel' . $module->getModule()->getType()}() < $module->getModule()->getLevel()) {
            return 'module_negative';
        }
        return '';
    }

    public function getModuleValueCalculator(): ModuleValueCalculatorInterface
    {
        return new ModuleValueCalculator();
    }

    public function getModuleLevels(): ?ShipRumpModuleLevelInterface
    {
        return $this->shipRumpModuleLevelRepository->getByShipRump($this->rump->getId());
    }
}
