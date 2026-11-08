<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\TechnologyProduction;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\TechnologyRecipeCost;
use Stu\Orm\Entity\TechnologyResearch;
use Stu\Orm\Entity\User;

interface TechnologyRepositoryInterface
{
    public function getRecipe(int $id): ?TechnologyRecipe;

    /** @return list<TechnologyRecipe> */
    public function getRecipes(): array;

    /** @return list<TechnologyRecipeCost> */
    public function getCosts(TechnologyRecipe $recipe): array;

    public function hasNaniteFabrication(User $user): bool;

    public function hasRunningResearch(User $user): bool;

    /** @return list<TechnologyResearch> */
    public function getResearchByUser(User $user): array;

    /** @return list<TechnologyResearch> */
    public function getRunningResearch(): array;

    /** @return list<TechnologyProduction> */
    public function getProductionByUser(User $user): array;

    /** @return list<TechnologyProduction> */
    public function getRunningProduction(bool $productionTick): array;

    /** @return list<TechnologyResearch|TechnologyProduction> */
    public function getRunningByField(PlanetField $field): array;

    public function save(TechnologyResearch|TechnologyProduction $job): void;

    public function deleteByUser(User $user): void;

    public function detachColony(Colony $colony): void;
}
