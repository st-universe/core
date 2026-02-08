<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;

final class ModuleFabricationListItem
{
    /** @var array<int> */
    private array $rump_ids = [];
    /** @var array<int> */
    private array $buildplan_ids = [];

    public function __construct(
        private ModuleQueueRepositoryInterface $moduleQueueRepository,
        private Module $module,
        private Colony $colony
    ) {}

    public function getModule(): Module
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

    /** @return array<int, ModuleCost> */
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

    public function addRump(SpacecraftRump $shipRump): void
    {
        if (!in_array($shipRump->getId(), $this->rump_ids)) {
            $this->rump_ids[] = $shipRump->getId();
        }
    }

    public function addBuildplan(SpacecraftBuildplan $buildplan): void
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
