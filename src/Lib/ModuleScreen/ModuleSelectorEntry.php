<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Override;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\ShipRumpModuleLevelInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;

final class ModuleSelectorEntry implements ModuleSelectorEntryInterface
{

    public function __construct(
        private ModuleSelectorInterface $moduleSelector,
        private ModuleInterface $module,
        private ShipRumpInterface $rump,
        private ShipRumpModuleLevelInterface $shipRumpModuleLevel,
        private ColonyInterface|ShipInterface $host,
        private UserInterface $user,
        private ModuleRepositoryInterface $moduleRepository,
        private ?ShipBuildplanInterface $buildplan = null
    ) {}


    #[Override]
    public function isChosen(): bool
    {
        if ($this->isDisabled()) {
            return false;
        }

        if ($this->buildplan !== null) {
            if ($this->module->getType() === ShipModuleTypeEnum::SPECIAL) {
                $allModulesWithSameName = $this->moduleRepository->findBy(['name' => $this->module->getName()]);

                $modules = $this->buildplan->getModules();
                foreach ($modules as $buildplanModule) {
                    foreach ($allModulesWithSameName as $moduleWithSameName) {
                        if ($buildplanModule->getModule()->getName() === $moduleWithSameName->getName()) {
                            return true;
                        }
                    }
                }
            } else {
                $modulesByType = $this->buildplan->getModulesByType($this->module->getType());
                foreach ($modulesByType as $buildplanModule) {
                    if ($buildplanModule->getModule()->getId() === $this->module->getId()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }



    #[Override]
    public function isDisabled(): bool
    {
        return $this->getStoredAmount() < 1;
    }

    #[Override]
    public function getStoredAmount(): int
    {
        /** @var StorageInterface|null */
        $storage = $this->host->getStorage()->get($this->module->getCommodityId());

        if ($storage === null) {
            return 0;
        }

        return $storage->getAmount();
    }

    #[Override]
    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    #[Override]
    public function getNeededCrew(): int
    {
        return $this->module->getCrewByFactionAndRumpLvl($this->user->getFaction(), $this->rump);
    }

    #[Override]
    public function getValue(): int
    {
        return $this->module
            ->getType()
            ->getModuleRumpWrapperCallable()($this->rump, $this->buildplan)
            ->getValue($this->module);
    }

    #[Override]
    public function getSecondvalue(): ?int
    {
        return $this->module
            ->getType()
            ->getModuleRumpWrapperCallable()($this->rump, $this->buildplan)
            ->getSecondValue($this->module);
    }

    #[Override]
    public function getModuleLevelClass(): string
    {
        $moduleLevels = $this->shipRumpModuleLevel;
        $module = $this->module;

        if ($moduleLevels->{'getModuleLevel' . $module->getType()->value}() > $module->getLevel()) {
            return 'module_positive';
        }
        if ($moduleLevels->{'getModuleLevel' . $module->getType()->value}() < $module->getLevel()) {
            return 'module_negative';
        }
        return '';
    }

    #[Override]
    public function getAddonValues(): array
    {
        $addon = $this->moduleSelector->getAddon();
        if ($addon === null) {
            return [];
        }

        return $addon->getModificators($this->module);
    }
}
