<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ModuleCostInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;

final class ModuleFabricationListItem
{
    /** @var array<int> */
    private array $rump_ids = [];
    /** @var array<int> */
    private array $buildplan_ids = [];

    public function __construct(
        private ModuleQueueRepositoryInterface $moduleQueueRepository,
        private ModuleInterface $module,
        private ColonyInterface $colony
    ) {}

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    public function getModuleId(): int
    {
        return $this->module->getId();
    }

    public function getModuleType(): int
    {
        return $this->module->getType()->value;
    }

    public function getModuleLevel(): int
    {
        return $this->module->getLevel();
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

    /** @return array<int, ModuleCostInterface> */
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

    public function addRump(ShipRumpInterface $shipRump): void
    {
        if (!in_array($shipRump->getId(), $this->rump_ids)) {
            $this->rump_ids[] = $shipRump->getId();
        }
    }

    public function addBuildplan(ShipBuildplanInterface $buildplan): void
    {
        if (!in_array($buildplan->getId(), $this->buildplan_ids)) {
            $this->buildplan_ids[] = $buildplan->getId();
        }
    }

    public function getClass(): string
    {
        return sprintf(
            'type_%d level_%d rump_%s buildplan_%s',
            $this->module->getType()->value,
            $this->getModuleLevel(),
            implode(' rump_', $this->rump_ids),
            implode(' buildplan_', $this->buildplan_ids)
        );
    }
}
