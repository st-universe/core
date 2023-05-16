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
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\WeaponShieldRepositoryInterface;

class ModuleSelector implements ModuleSelectorInterface
{
    private const MACRO = 'html/modulescreen.xhtml/moduleselector';
    private const TEMPLATE = 'html/ajaxempty.xhtml';

    /** @var ModuleSelectorWrapperInterface[] */
    private $moduleSelectorWrappers;
    private int $moduleType;
    private ShipRumpInterface $rump;
    private int $userId;
    private ?ColonyInterface $colony;
    private ?ShipInterface $station;
    private ?ShipBuildplanInterface $buildplan;
    private ?WeaponShieldRepositoryInterface $weaponshield;

    private ModuleRepositoryInterface $moduleRepository;

    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    private TalPageInterface $talPage;

    public function __construct(
        ModuleRepositoryInterface $moduleRepository,
        ?WeaponShieldRepositoryInterface $weaponshield,
        ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        TalPageInterface $talPage,
        int $moduleType,
        ?ColonyInterface $colony,
        ?ShipInterface $station,
        ShipRumpInterface $rump,
        int $userId,
        ?ShipBuildplanInterface $buildplan = null
    ) {
        $this->weaponshield = $weaponshield;
        $this->moduleType = $moduleType;
        $this->rump = $rump;
        $this->userId = $userId;
        $this->colony = $colony;
        $this->station = $station;
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
        return $this->userId;
    }

    public function getRump(): ShipRumpInterface
    {
        return $this->rump;
    }

    public function getFactionbyWeapon($module): ?WeaponShieldRepositoryInterface
    {
        return $this->weaponshield->getFactionByModule($module);
    }

    private function getShipRumpRoleId(): int
    {
        $shipRumpRole = $this->getRump()->getShipRumpRole();

        if ($shipRumpRole === null) {
            throw new InvalidArgumentException('invalid rump without rump role');
        }

        return $shipRumpRole->getId();
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
                        $this->getShipRumpRoleId()
                    );
                } elseif ($this->station !== null) {
                    $modules = $this->moduleRepository->getBySpecialTypeShipAndRump(
                        $this->station->getId(),
                        $this->getModuleType(),
                        $this->getRump()->getId(),
                        $this->getShipRumpRoleId()
                    );
                }
            } else {
                if ($this->getColony() !== null) {
                    $mod_level = $this->shipRumpModuleLevelRepository->getByShipRump(
                        $this->getRump()->getId()
                    );

                    $min_level = $mod_level->{'getModuleLevel' . $this->getModuleType() . 'Min'}();
                    $max_level = $mod_level->{'getModuleLevel' . $this->getModuleType() . 'Max'}();

                    $modules = $this->moduleRepository->getByTypeColonyAndLevel(
                        $this->getColony()->getId(),
                        $this->getModuleType(),
                        $this->getShipRumpRoleId(),
                        range($min_level, $max_level)
                    );
                }
            }
            foreach ($modules as $obj) {
                $this->moduleSelectorWrappers[$obj->getId()] = new ModuleSelectorWrapper($obj, $this->getBuildplan());
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
}