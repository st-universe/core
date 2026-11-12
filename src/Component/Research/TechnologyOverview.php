<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use LogicException;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\SpecialResearchDefinition;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;

final class TechnologyOverview
{
    public function __construct(
        private TechnologyRepositoryInterface $repository,
        private TechnologyCatalog $catalog,
        private TechnologyMaterials $materials,
        private ModuleOptimizationProgress $progress,
        private TechnologyFacility $facility,
        private CommodityRepositoryInterface $commodities,
        private ModuleRepositoryInterface $modules,
        private SpecialResearchCatalog $foundations
    ) {}

    public function hasRunningResearch(User $user): bool
    {
        return $this->repository->hasRunningResearch($user);
    }

    public function setTemplateVariables(Colony $colony, ?PlanetField $field, TemplateInterface $template, User $user): void
    {
        $recipes = [];
        $optimization = null;
        foreach ($this->catalog->getRecipes() as $recipe) {
            if (!$this->catalog->canUse($user, $recipe)) {
                continue;
            }
            if ($recipe->getKind() === TechnologyRecipeKindEnum::MODULE_OPTIMIZATION) {
                $optimization = $recipe;
            } else {
                $recipes[] = $this->recipeRow($colony, $field, $recipe);
            }
        }
        $template->setTemplateVar('TECHNOLOGY_RECIPES', $recipes);
        $template->setTemplateVar('TECHNOLOGY_RESEARCH_JOBS', array_filter($this->repository->getResearchByUser($user), static fn ($job): bool => $job->getStatus() === SpecialResearchStatusEnum::RUNNING));
        $template->setTemplateVar('TECHNOLOGY_PRODUCTION_JOBS', array_filter($this->repository->getProductionByUser($user), static fn ($job): bool => $job->getKind() !== TechnologyRecipeKindEnum::NANITE_PRODUCTION));
        $template->setTemplateVar('NANITE_FABRICATION_COMPLETE', $this->repository->hasNaniteFabrication($user));
        $template->setTemplateVar('OPTIMIZATION_RECIPE', $optimization);
        $template->setTemplateVar('OPTIMIZATION_MODULES', $optimization === null ? [] : $this->moduleRows($colony, $optimization));
        $template->setTemplateVar('TECHNOLOGY_FIELD_ACTIVE', $field !== null && $field->isActive());
        $template->setTemplateVar('TECHNOLOGY_IS_CENTER', $field?->getBuilding()?->getFunctions()->containsKey(BuildingFunctionEnum::TECH_CENTER->value) ?? false);
        $template->setTemplateVar('TECHNOLOGY_RESEARCH_BUSY', $this->repository->hasRunningResearch($user));
    }

    /** @return array<string, mixed> */
    private function recipeRow(Colony $colony, ?PlanetField $field, TechnologyRecipe $recipe): array
    {
        $costs = $this->materials->getCosts($recipe);
        return [
            'recipe' => $recipe,
            'costs' => $this->costRows($colony, $costs),
            'canPay' => $this->materials->canPay($colony, $costs, $recipe->getEnergyCost()),
            'fieldReady' => $field !== null && $this->facility->canStart($colony, $field, $recipe)
        ];
    }

    /** @param array<int, int> $costs
     * @return list<array<string, mixed>>
     */
    private function costRows(Colony $colony, array $costs): array
    {
        $rows = [];
        foreach ($costs as $id => $amount) {
            $rows[] = ['commodity' => $this->commodities->find($id), 'amount' => $amount, 'stored' => $colony->getStorage()->get($id)?->getAmount() ?? 0];
        }
        return $rows;
    }

    /** @return list<array<string, mixed>> */
    private function moduleRows(Colony $colony, TechnologyRecipe $recipe): array
    {
        $rows = [];
        $user = $colony->getUser();
        $foundations = $this->catalog->getFoundations($user);
        $definitions = $this->foundations->getDefinitions($user->getFactionId());
        foreach ($this->modules->getStandardAndFactionByLevels($user->getFactionId(), range(1, 6)) as $module) {
            if (!$this->catalog->supportsOptimization($user, $module, $foundations)) {
                continue;
            }
            $system = $this->catalog->getSystem($module);
            $definition = $system === null ? null : ($definitions[$system->value] ?? null);
            if ($definition === null) {
                continue;
            }
            $steps = $this->progress->getImprovementSteps($user, $module);
            $rows[] = [
                'module' => $module,
                'steps' => $steps,
                'percent' => number_format($steps / 10, 1, ',', ''),
                'baseChance' => min($recipe->getSettings()['max_base_success'], $recipe->getSettings()['base_success'] + $steps),
                'data' => $definition->getDataCommodity(),
                'canImprove' => $steps < $recipe->getSettings()['max_steps'],
                'stored' => $colony->getStorage()->get($module->getCommodityId())?->getAmount() ?? 0,
                'canOptimize' => $this->catalog->canOptimize($user, $module, $foundations),
                'canPayMinimum' => $this->canPayOptimizationMinimum($colony, $recipe, $module, $definition)
            ];
        }
        return $rows;
    }

    private function canPayOptimizationMinimum(Colony $colony, TechnologyRecipe $recipe, Module $module, SpecialResearchDefinition $definition): bool
    {
        $costs = $this->materials->getCosts($recipe);
        $settings = $recipe->getSettings();
        $nanites = $recipe->getNaniteCommodity() ?? throw new LogicException('Optimization nanite commodity missing');
        foreach ([
            [$module->getCommodityId(), $settings['min_modules']],
            [$nanites->getId(), $settings['min_nanites']],
            [$definition->getDataCommodity()->getId(), $settings['data_amount']]
        ] as [$commodityId, $amount]) {
            $costs[$commodityId] = ($costs[$commodityId] ?? 0) + $amount;
        }
        return $this->materials->canPay($colony, $costs, $recipe->getEnergyCost());
    }
}
