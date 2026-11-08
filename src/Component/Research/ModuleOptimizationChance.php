<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use InvalidArgumentException;
use Stu\Orm\Entity\TechnologyRecipe;

final class ModuleOptimizationChance
{
    public function calculate(TechnologyRecipe $recipe, int $steps, int $modules, int $nanites): int
    {
        $s = $recipe->getSettings();
        if ($modules < $s['min_modules'] || $modules > $s['max_modules'] || $nanites < $s['min_nanites'] || $nanites > $s['max_nanites']) {
            throw new InvalidArgumentException('Der Materialeinsatz liegt außerhalb des erlaubten Bereichs');
        }
        $base = min($s['max_base_success'], $s['base_success'] + max(0, $steps));
        $moduleBonus = ($modules - $s['min_modules']) / ($s['max_modules'] - $s['min_modules']);
        $naniteBonus = ($nanites - $s['min_nanites']) / ($s['max_nanites'] - $s['min_nanites']);
        return (int) round($base * 100 + ($moduleBonus + $naniteBonus) * $s['extra_success'] * 50, 0, PHP_ROUND_HALF_UP);
    }
}
