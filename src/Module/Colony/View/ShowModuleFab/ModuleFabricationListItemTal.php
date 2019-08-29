<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use ColonyData;
use ModuleQueue;
use ModulesData;

final class ModuleFabricationListItemTal
{
    private $colony;

    private $module;

    public function __construct(
        ModulesData $module,
        ColonyData $colony
    ) {
        $this->colony = $colony;
        $this->module = $module;
    }

    public function getModule(): ModulesData
    {
        return $this->module;
    }

    public function getModuleId(): int
    {
        return (int)$this->module->getId();
    }

    public function getGoodId(): int
    {
        return (int)$this->module->getGoodId();
    }

    public function getName(): string
    {
        return $this->module->getName();
    }

    public function getEnergyCost(): int
    {
        return (int)$this->module->getEcost();
    }

    public function getConstructionCosts(): array
    {
        return $this->module->getCost();
    }

    public function getAmountQueued(): int
    {
        return ModuleQueue::getAmountByColonyAndModule($this->colony->getId(), $this->module->getId());
    }

    public function getAmountInStock(): int
    {
        $result = $this->colony->getStorage()[$this->getGoodId()] ?? null;

        if ($result === null) {
            return 0;
        }
        return (int) $result->getAmount();
    }
}