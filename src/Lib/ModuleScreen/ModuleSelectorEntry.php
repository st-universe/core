<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Override;
use request;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\ShipRumpModuleLevelInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\UserInterface;

final class ModuleSelectorEntry implements ModuleSelectorEntryInterface
{

    public function __construct(
        private ModuleSelectorInterface $moduleSelector,
        private ModuleInterface $module,
        private ShipRumpInterface $rump,
        private ShipRumpModuleLevelInterface $shipRumpModuleLevel,
        private ColonyInterface|ShipInterface $host,
        private UserInterface $user,
        private ?ShipBuildplanInterface $buildplan = null
    ) {
    }

    #[Override]
    public function isChosen(): bool
    {
        if ($this->isDisabled()) {
            return false;
        }

        if ($this->buildplan !== null) {
            $module_id_list = array_map(
                fn (BuildplanModuleInterface $buildplanModule): int => $buildplanModule->getModuleId(),
                $this->buildplan->getModulesByType($this->module->getType())
            );
            if (in_array($this->module->getId(), $module_id_list)) {
                return true;
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
        $storage = $this->host->getStorage()->get($this->getModule()->getCommodityId());

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
        return $this->getModule()->getCrewByFactionAndRumpLvl($this->user->getFaction(), $this->rump);
    }

    #[Override]
    public function getValue(): int
    {
        return $this->getModule()
            ->getType()
            ->getModuleRumpWrapperCallable()($this->rump, $this->buildplan)
            ->getValue($this->getModule());
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

        return $addon->getModificators($this->getModule());
    }
}
