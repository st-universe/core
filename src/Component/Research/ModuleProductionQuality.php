<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use InvalidArgumentException;
use LogicException;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\TechnologyRepositoryInterface;

final class ModuleProductionQuality
{
    public function __construct(
        private ModuleOptimizationProgress $progress,
        private TechnologyRepositoryInterface $recipes
    ) {}

    public function getImprovementSteps(User $user, Module $module): int
    {
        return $this->progress->getImprovementSteps($user, $module);
    }

    public function getNaniteCost(int $steps): int
    {
        return $this->calculateNaniteCost($steps, $this->getRecipe());
    }

    private function calculateNaniteCost(int $steps, ?TechnologyRecipe $recipe): int
    {
        if ($steps < 0 || $steps > 70) {
            throw new InvalidArgumentException('Invalid production module quality');
        }
        $settings = $recipe?->getSettings() ?? [];
        $free = $this->setting($settings, 'production_nanite_free_steps', 4, 0, 70);
        $size = $this->setting($settings, 'production_nanite_step_size', 10, 1);
        $amount = $this->setting($settings, 'production_nanite_amount', 1, 0);
        return $steps <= $free ? 0 : (intdiv($steps - $free - 1, $size) + 1) * $amount;
    }

    /** @param array<string, int> $settings */
    private function setting(array $settings, string $name, int $default, int $minimum, int $maximum = PHP_INT_MAX): int
    {
        $value = $settings[$name] ?? $default;
        return $value >= $minimum && $value <= $maximum ? $value : $default;
    }

    public function getNaniteCommodity(): ?Commodity
    {
        return $this->getRecipe()?->getNaniteCommodity();
    }

    /** @return array<int, ModuleCost> */
    public function getCosts(Module $module, int $steps): array
    {
        $costs = [];
        foreach ($module->getCost() as $cost) {
            $this->addCost($costs, $cost->getCommodity(), $cost->getAmount());
        }
        $recipe = $this->getRecipe();
        $nanites = $this->calculateNaniteCost($steps, $recipe);
        if ($nanites > 0) {
            $commodity = $recipe?->getNaniteCommodity() ?? throw new LogicException('Nanitenware für die optimierte Modulproduktion fehlt');
            $this->addCost($costs, $commodity, $nanites);
        }
        return $costs;
    }

    /** @param array<int, ModuleCost> $costs */
    private function addCost(array &$costs, Commodity $commodity, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }
        $id = $commodity->getId();
        $amount += ($costs[$id] ?? null)?->getAmount() ?? 0;
        $costs[$id] = new ModuleCost()->setCommodity($commodity)->setAmount($amount);
    }

    private function getRecipe(): ?TechnologyRecipe
    {
        return array_find($this->recipes->getRecipes(), static fn (TechnologyRecipe $recipe): bool => $recipe->getKind() === TechnologyRecipeKindEnum::MODULE_OPTIMIZATION);
    }
}
