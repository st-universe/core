<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;

final class ModuleFabricationListItemTal
{
    public function __construct(private ModuleQueueRepositoryInterface $moduleQueueRepository, private ModuleInterface $module, private ColonyInterface $colony)
    {
    }

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    public function getModuleId(): int
    {
        return $this->module->getId();
    }

    public function getCommodityId(): int
    {
        return $this->module->getCommodityId();
    }

    public function getName(): string
    {
        return $this->module->getName();
    }

    public function getEnergyCost(): int
    {
        return $this->module->getEcost();
    }

    public function getConstructionCosts(): array
    {
        return $this->module->getCostSorted();
    }

    public function getAmountQueued(): int
    {
        return $this->moduleQueueRepository->getAmountByColonyAndModule(
            $this->colony->getId(),
            $this->module->getId()
        );
    }

    public function getAmountInStock(): int
    {
        $result = $this->colony->getStorage()[$this->getCommodityId()] ?? null;

        if ($result === null) {
            return 0;
        }
        return $result->getAmount();
    }
}
