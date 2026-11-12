<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Orm\Entity\TechnologyRecipe;

final class TechnologyRecipeValidator
{
    public function isValid(TechnologyRecipe $recipe): bool
    {
        if ($recipe->getEnergyCost() < 0 || $recipe->getDuration() < 0 || $recipe->getOutputAmount() < 0) {
            return false;
        }
        return match ($recipe->getKind()) {
            TechnologyRecipeKindEnum::NANITE_RESEARCH => $this->validResearch($recipe),
            TechnologyRecipeKindEnum::MODULE_OPTIMIZATION => $this->validOptimization($recipe),
            TechnologyRecipeKindEnum::DATASET_COPY => $this->validCopy($recipe)
        };
    }

    private function validResearch(TechnologyRecipe $recipe): bool
    {
        return $recipe->getDuration() > 0 && $recipe->getFailureChance() >= 0 && $recipe->getFailureChance() <= 100;
    }

    private function validCopy(TechnologyRecipe $recipe): bool
    {
        return $recipe->getResearchType() !== null && $recipe->getDuration() > 0 && $this->validOutput($recipe);
    }

    private function validOutput(TechnologyRecipe $recipe): bool
    {
        return $recipe->getOutputAmount() > 0 && $recipe->getOutputCommodity() !== null;
    }

    private function validOptimization(TechnologyRecipe $recipe): bool
    {
        $s = $recipe->getSettings();
        $keys = ['min_modules', 'max_modules', 'min_nanites', 'max_nanites', 'base_success', 'max_base_success', 'extra_success', 'max_steps', 'accident_chance', 'data_amount'];
        if (array_diff($keys, array_keys($s)) !== [] || $recipe->getNaniteCommodity() === null || $recipe->getDuration() < 1) {
            return false;
        }
        return $this->validAmounts($s) && $this->validChances($s);
    }

    /** @param array<string, int> $s */
    private function validAmounts(array $s): bool
    {
        return $s['min_modules'] > 0 && $s['max_modules'] > $s['min_modules']
            && $s['min_nanites'] > 0 && $s['max_nanites'] > $s['min_nanites']
            && $s['max_steps'] > 0 && $s['max_steps'] <= 70 && $s['data_amount'] > 0;
    }

    /** @param array<string, int> $s */
    private function validChances(array $s): bool
    {
        return $s['base_success'] >= 0 && $s['max_base_success'] >= $s['base_success']
            && $s['extra_success'] >= 0 && $s['max_base_success'] + $s['extra_success'] <= 100
            && $s['accident_chance'] >= 0 && $s['accident_chance'] <= 100;
    }
}
