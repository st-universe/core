<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleBuildingFunction;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleCostRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;

final class NaniteProductionSetup
{
    public function __construct(
        private TechnologyRepositoryInterface $technology,
        private ModuleRepositoryInterface $modules,
        private ModuleCostRepositoryInterface $costs,
        private ModuleBuildingFunctionRepositoryInterface $functions,
        private ModuleQueueRepositoryInterface $queues,
        private EntityManagerInterface $entityManager
    ) {}

    public function setup(): Module
    {
        $this->modules->synchronizeProductionSequences();
        $recipe = array_find($this->technology->getRecipes(), static fn (TechnologyRecipe $recipe): bool => $recipe->getKind() === TechnologyRecipeKindEnum::NANITE_PRODUCTION)
            ?? throw new LogicException('Nanitenrezept fehlt');
        $commodity = $recipe->getOutputCommodity() ?? throw new LogicException('Nanitenware fehlt');
        $amount = $recipe->getOutputAmount();
        if ($amount <= 0 || $recipe->getEnergyCost() % $amount !== 0) {
            throw new LogicException('Nanitenrezept lässt sich nicht auf einzelne Naniten umrechnen');
        }
        $module = $this->modules->findOneBy(['commodity_id' => $commodity->getId(), 'type' => SpacecraftModuleTypeEnum::SPECIAL, 'level' => 0]) ?? new Module();
        $module->setName($commodity->getName())->setType(SpacecraftModuleTypeEnum::SPECIAL)
            ->setLevel(0)->setCommodity($commodity)->setResearch(null)->setViewable(false)
            ->setEcost(intdiv($recipe->getEnergyCost(), $amount));
        $this->modules->save($module);
        $this->entityManager->flush();

        foreach ($this->costs->getByModule($module->getId()) as $cost) {
            $module->getCost()->removeElement($cost);
            $this->costs->delete($cost);
        }
        foreach ($this->technology->getCosts($recipe) as $recipeCost) {
            if ($recipeCost->getAmount() <= 0 || $recipeCost->getAmount() % $amount !== 0) {
                throw new LogicException('Nanitenbaukosten lassen sich nicht auf einzelne Naniten umrechnen');
            }
            $cost = new ModuleCost()->setModule($module)->setCommodity($recipeCost->getCommodity())
                ->setAmount(intdiv($recipeCost->getAmount(), $amount));
            $this->costs->save($cost);
            $module->getCost()->add($cost);
        }
        $function = $this->functions->findOneBy(['module' => $module, 'buildingfunction' => BuildingFunctionEnum::TECH_CENTER->value]);
        if ($function === null) {
            $this->functions->save(new ModuleBuildingFunction()->setModule($module)->setBuildingFunction(BuildingFunctionEnum::TECH_CENTER->value));
        }
        $this->entityManager->flush();
        return $module;
    }

    public function transferPending(Module $module): int
    {
        $transferred = 0;
        foreach ($this->technology->getRunningProduction(true) as $job) {
            $colony = $job->getColony();
            if ($job->getKind() !== TechnologyRecipeKindEnum::NANITE_PRODUCTION || $colony === null
                || $colony->getUser()->getId() !== $job->getUser()->getId()
                || $job->getOutputCommodity()->getId() !== $module->getCommodityId()) {
                continue;
            }
            $queue = $this->queues->getByColonyAndModuleAndBuilding($colony->getId(), $module->getId(), BuildingFunctionEnum::TECH_CENTER->value, 0);
            if ($queue === null) {
                $queue = $this->queues->prototype()->setColony($colony)->setModule($module)->setBuildingFunction(BuildingFunctionEnum::TECH_CENTER);
            }
            $queue->setAmount($queue->getAmount() + $job->getOutputAmount());
            $this->queues->save($queue);
            $this->technology->deleteProduction($job);
            $transferred += $job->getOutputAmount();
            $this->entityManager->flush();
        }
        return $transferred;
    }
}
