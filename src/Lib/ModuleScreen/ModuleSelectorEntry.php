<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Override;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ModuleRepositoryInterface;

final class ModuleSelectorEntry implements ModuleSelectorEntryInterface
{

    public function __construct(
        private ModuleSelectorInterface $moduleSelector,
        private Module $module,
        private SpacecraftRump $rump,
        private ShipRumpModuleLevel $shipRumpModuleLevel,
        private Colony|Spacecraft $host,
        private User $user,
        private ModuleRepositoryInterface $moduleRepository,
        private ?SpacecraftBuildplan $buildplan = null
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
        /** @var Storage|null */
        $storage = $this->host->getStorage()->get($this->module->getCommodityId());

        if ($storage === null) {
            return 0;
        }

        return $storage->getAmount();
    }

    #[Override]
    public function getModule(): Module
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
