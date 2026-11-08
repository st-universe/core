<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use InvalidArgumentException;
use LogicException;
use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\User;

final class TechnologyOrderBuilder
{
    public function __construct(
        private TechnologyMaterials $materials,
        private TechnologyCatalog $catalog,
        private SpecialResearchCatalog $foundationCatalog,
        private ModuleOptimizationProgress $progress,
        private ModuleOptimizationChance $chance
    ) {}

    public function build(User $user, TechnologyRecipe $recipe, ?Module $module, int $modules, int $nanites, InformationInterface $information): ?TechnologyOrder
    {
        if ($recipe->getKind() === TechnologyRecipeKindEnum::MODULE_OPTIMIZATION) {
            return $this->optimization($user, $recipe, $module, $modules, $nanites, $information);
        }
        $costs = $this->materials->getCosts($recipe);
        if ($costs === []) {
            $information->addInformation('Für dieses Verfahren sind keine gültigen Materialkosten hinterlegt');
            return null;
        }
        $foundation = array_values($this->catalog->getFoundations($user))[0] ?? null;
        return new TechnologyOrder($costs, system: $recipe->getResearchType() ?? $foundation?->getResearchType(), chance: (100 - $recipe->getFailureChance()) * 100);
    }

    private function optimization(User $user, TechnologyRecipe $recipe, ?Module $module, int $modules, int $nanites, InformationInterface $information): ?TechnologyOrder
    {
        if ($module === null || !$this->catalog->canOptimize($user, $module)) {
            $information->addInformation('Dieses Modul kann noch nicht optimiert werden');
            return null;
        }
        $steps = $this->progress->getImprovementSteps($user, $module);
        if ($steps >= $recipe->getSettings()['max_steps']) {
            $information->addInformation('Dieses Modul hat die maximale Verbesserung erreicht');
            return null;
        }
        try {
            $chance = $this->chance->calculate($recipe, $steps, $modules, $nanites);
        } catch (InvalidArgumentException $e) {
            $information->addInformation($e->getMessage());
            return null;
        }
        $system = $this->catalog->getSystem($module) ?? throw new LogicException('Optimization system missing');
        $definition = $this->foundationCatalog->getDefinition($system, $user->getFactionId());
        if ($definition === null) {
            $information->addInformation('Der zugehörige Forschungsdatensatz ist nicht konfiguriert');
            return null;
        }
        $costs = $this->materials->getCosts($recipe);
        $this->add($costs, $module->getCommodityId(), $modules);
        $this->add($costs, $recipe->getNaniteCommodity()?->getId() ?? throw new LogicException('Nanite commodity missing'), $nanites);
        $this->add($costs, $definition->getDataCommodity()->getId(), $recipe->getSettings()['data_amount']);
        return new TechnologyOrder($costs, $module, $system, $chance, $steps);
    }

    /** @param array<int, int> $costs */
    private function add(array &$costs, int $commodityId, int $amount): void
    {
        $costs[$commodityId] = ($costs[$commodityId] ?? 0) + $amount;
    }
}
