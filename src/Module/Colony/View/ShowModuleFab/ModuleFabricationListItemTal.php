<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;

final class ModuleFabricationListItemTal
{
    private ModuleQueueRepositoryInterface $moduleQueueRepository;

    private ColonyInterface $colony;

    private ModuleInterface $module;

    public function __construct(
        ModuleQueueRepositoryInterface $moduleQueueRepository,
        ModuleInterface $module,
        ColonyInterface $colony
    ) {
        $this->colony = $colony;
        $this->module = $module;
        $this->moduleQueueRepository = $moduleQueueRepository;
    }

    public function getModule(): ModuleInterface
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
        return $this->module->getCostSorted();
    }

    public function getAmountQueued(): int
    {
        return $this->moduleQueueRepository->getAmountByColonyAndModule(
            (int) $this->colony->getId(),
            (int) $this->module->getId()
        );
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
