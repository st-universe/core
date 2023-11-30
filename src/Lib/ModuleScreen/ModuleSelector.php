<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use InvalidArgumentException;
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
use Stu\Orm\Entity\ShipRumpRoleInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;

class ModuleSelector implements ModuleSelectorInterface
{
    private const MACRO = 'html/modulescreen.xhtml/moduleselector';
    private const TEMPLATE = 'html/ajaxempty.xhtml';

    /** @var ModuleSelectorWrapperInterface[]|null */
    private ?array $moduleSelectorWrappers = null;
    private int $moduleType;
    private ShipRumpInterface $rump;
    private UserInterface $user;
    private ?ColonyInterface $colony;
    private ?ShipInterface $station;
    private ?ShipBuildplanInterface $buildplan;
    private ?ModuleSelectorAddonInterface $addon;

    private ModuleRepositoryInterface $moduleRepository;

    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    private TalPageInterface $talPage;

    public function __construct(
        ModuleRepositoryInterface $moduleRepository,
        ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        TalPageInterface $talPage,
        int $moduleType,
        ?ColonyInterface $colony,
        ?ShipInterface $station,
        ShipRumpInterface $rump,
        UserInterface $user,
        ?ModuleSelectorAddonInterface $addon,
        ?ShipBuildplanInterface $buildplan = null
    ) {
        $this->moduleType = $moduleType;
        $this->rump = $rump;
        $this->user = $user;
        $this->colony = $colony;
        $this->station = $station;
        $this->buildplan = $buildplan;
        $this->moduleRepository = $moduleRepository;
        $this->addon = $addon;
        $this->shipRumpModuleLevelRepository = $shipRumpModuleLevelRepository;
        $this->talPage = $talPage;
    }

    public function allowMultiple(): bool
    {
        return false;
    }

    public function getMacro(): string
    {
        return self::MACRO;
    }

    public function render(): string
    {
        $this->talPage->setTemplate(self::TEMPLATE);
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
        return $this->user->getId();
    }

    public function getRump(): ShipRumpInterface
    {
        return $this->rump;
    }

    private function getShipRumpRole(): ShipRumpRoleInterface
    {
        $shipRumpRole = $this->getRump()->getShipRumpRole();

        if ($shipRumpRole === null) {
            throw new InvalidArgumentException('invalid rump without rump role');
        }

        return $shipRumpRole;
    }

    public function getAvailableModules(): array
    {
        if ($this->moduleSelectorWrappers === null) {
            $this->moduleSelectorWrappers = [];
            $modules = [];
            if ($this->getModuleType() == ShipModuleTypeEnum::MODULE_TYPE_SPECIAL) {
                if ($this->getColony() !== null) {
                    $modules = $this->moduleRepository->getBySpecialTypeColonyAndRump(
                        $this->getColony()->getId(),
                        $this->getModuleType(),
                        $this->getRump()->getId(),
                        $this->getShipRumpRole()->getId()
                    );
                } elseif ($this->station !== null) {
                    $modules = $this->moduleRepository->getBySpecialTypeShipAndRump(
                        $this->station->getId(),
                        $this->getModuleType(),
                        $this->getRump()->getId(),
                        $this->getShipRumpRole()
                    );
                }
            } elseif ($this->getColony() !== null) {
                $mod_level = $this->shipRumpModuleLevelRepository->getByShipRump(
                    $this->getRump()->getId()
                );
                $min_level = $mod_level->{'getModuleLevel' . $this->getModuleType() . 'Min'}();
                $max_level = $mod_level->{'getModuleLevel' . $this->getModuleType() . 'Max'}();
                $modules = $this->moduleRepository->getByTypeColonyAndLevel(
                    $this->getColony()->getId(),
                    $this->getModuleType(),
                    $this->getShipRumpRole()->getId(),
                    range($min_level, $max_level)
                );
            }
            foreach ($modules as $obj) {
                $this->moduleSelectorWrappers[$obj->getId()] = new ModuleSelectorWrapper(
                    $obj,
                    $this->getRump(),
                    $this->user,
                    $this->getBuildplan()
                );
            }
        }
        return $this->moduleSelectorWrappers;
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

    public function getAddon(): ?ModuleSelectorAddonInterface
    {
        return $this->addon;
    }
}
