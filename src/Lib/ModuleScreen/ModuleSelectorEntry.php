<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

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
    private ModuleSelectorInterface $moduleSelector;

    private ModuleInterface $module;

    private ShipRumpInterface $rump;

    private ColonyInterface|ShipInterface $host;

    private ShipRumpModuleLevelInterface $shipRumpModuleLevel;

    private UserInterface $user;

    private ?ShipBuildplanInterface $buildplan;

    public function __construct(
        ModuleSelectorInterface $moduleSelector,
        ModuleInterface $module,
        ShipRumpInterface $rump,
        ShipRumpModuleLevelInterface $shipRumpModuleLevel,
        ColonyInterface|ShipInterface $host,
        UserInterface $user,
        ?ShipBuildplanInterface $buildplan = null
    ) {
        $this->moduleSelector = $moduleSelector;
        $this->module = $module;
        $this->rump = $rump;
        $this->shipRumpModuleLevel = $shipRumpModuleLevel;
        $this->host = $host;
        $this->user = $user;
        $this->buildplan = $buildplan;
    }

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

        //$request = request::postArray('mod_' . $this->module->getType()->value);
        //return array_key_exists($this->module->getId(), $request);

        return false;
    }

    public function isDisabled(): bool
    {
        return $this->getStoredAmount() < 1;
    }

    public function getStoredAmount(): int
    {
        /** @var StorageInterface|null */
        $storage = $this->host->getStorage()->get($this->getModule()->getCommodityId());

        if ($storage === null) {
            return 0;
        }

        return $storage->getAmount();
    }

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    public function getNeededCrew(): int
    {
        return $this->getModule()->getCrewByFactionAndRumpLvl($this->user->getFaction(), $this->rump);
    }

    public function getValue(): int
    {
        return $this->getModule()
            ->getType()
            ->getModuleRumpWrapperCallable()($this->rump, $this->buildplan)
            ->getValue($this->getModule());
    }

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

    public function getAddonValues(): array
    {
        $addon = $this->moduleSelector->getAddon();
        if ($addon === null) {
            return [];
        }

        return $addon->getModificators($this->getModule());
    }
}
