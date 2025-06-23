<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Override;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\ShipRumpModuleLevelInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;

final class ModuleSelectorEntry implements ModuleSelectorEntryInterface
{

    public function __construct(
        private ModuleSelectorInterface $moduleSelector,
        private ModuleInterface $module,
        private SpacecraftRumpInterface $rump,
        private ShipRumpModuleLevelInterface $shipRumpModuleLevel,
        private ColonyInterface|SpacecraftInterface $host,
        private UserInterface $user,
        private ModuleRepositoryInterface $moduleRepository,
        private ?SpacecraftBuildplanInterface $buildplan = null
    ) {}


    #[Override]
    public function isChosen(): bool
    {
        if ($this->buildplan !== null) {
            if ($this->module->getType()->isSpecialSystemType()) {
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
                foreach ($modulesByType as $module) {
                    if ($module->getId() === $this->module->getId()) {
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

        if ($moduleLevels->getDefaultLevel($module->getType()) > $module->getLevel()) {
            return 'module_positive';
        }
        if ($moduleLevels->getDefaultLevel($module->getType()) < $module->getLevel()) {
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
