<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use Stu\Component\Research\ModuleProductionQuality;
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
    /** @var array<int, int> */
    private array $required_amounts_by_rump = [];
    /** @var array<int, int> */
    private array $required_amounts_by_buildplan = [];

    public function __construct(
        private ModuleQueueRepositoryInterface $moduleQueueRepository,
        private Module $module,
        private Colony $colony,
        private int $amountInStockAccountWide = 0,
        private ?ModuleProductionQuality $productionQuality = null
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

    /** @return list<array{optimized: bool, label: string, costs: array<int, ModuleCost>}> */
    public function getProductionVariants(): array
    {
        $variants = [['optimized' => false, 'label' => 'Standard (+0 %)', 'costs' => $this->getConstructionCosts()]];
        $steps = $this->productionQuality?->getImprovementSteps($this->colony->getUser(), $this->module) ?? 0;
        if ($steps > 0) {
            $variants[] = [
                'optimized' => true,
                'label' => sprintf('Fertigungsbonus (+%s %%)', number_format($steps / 10, 1, ',', '')),
                'costs' => $this->productionQuality->getCosts($this->module, $steps)
            ];
        }
        return $variants;
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
        return $this->getAmountInStockByCommodityId($this->getCommodityId());
    }

    public function getAmountInStockByCommodityId(int $commodityId): int
    {
        $result = $this->colony->getStorage()[$commodityId] ?? null;

        if ($result === null) {
            return 0;
        }
        return $result->getAmount();
    }

    public function getAmountInStockAccountWide(): int
    {
        return $this->amountInStockAccountWide;
    }

    public function addRump(SpacecraftRump $shipRump, int $requiredAmount = 1): void
    {
        $rumpId = $shipRump->getId();

        if (!in_array($rumpId, $this->rump_ids, true)) {
            $this->rump_ids[] = $rumpId;
        }

        $this->addRequiredAmount($this->required_amounts_by_rump, $rumpId, $requiredAmount);
    }

    public function addBuildplan(SpacecraftBuildplan $buildplan, int $requiredAmount = 1): void
    {
        $buildplanId = $buildplan->getId();

        if (!in_array($buildplanId, $this->buildplan_ids, true)) {
            $this->buildplan_ids[] = $buildplanId;
        }

        $this->addRequiredAmount($this->required_amounts_by_buildplan, $buildplanId, $requiredAmount);
    }

    /** @return array<int, int> */
    public function getRequiredAmountsByRump(): array
    {
        return $this->required_amounts_by_rump;
    }

    /** @return array<int, int> */
    public function getRequiredAmountsByBuildplan(): array
    {
        return $this->required_amounts_by_buildplan;
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

    /**
     * @param array<int, int> $requiredAmounts
     */
    private function addRequiredAmount(array &$requiredAmounts, int $id, int $requiredAmount): void
    {
        if ($requiredAmount <= 1) {
            return;
        }

        $requiredAmounts[$id] = max($requiredAmounts[$id] ?? 1, $requiredAmount);
    }
}
