<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserModuleOptimization;
use Stu\Orm\Repository\TechnologyRepositoryInterface;
use Stu\Orm\Repository\UserModuleOptimizationRepositoryInterface;
use Stu\StuTestCase;

final class ModuleProductionQualityTest extends StuTestCase
{
    /** @return iterable<array{int, int}> */
    public static function priceBoundaries(): iterable
    {
        yield [0, 0];
        yield [4, 0];
        yield [5, 1];
        yield [14, 1];
        yield [15, 2];
        yield [24, 2];
        yield [25, 3];
        yield [64, 6];
        yield [65, 7];
        yield [70, 7];
    }

    #[DataProvider('priceBoundaries')]
    public function testDefaultPricesFollowEveryBoundary(int $steps, int $expected): void
    {
        self::assertSame($expected, $this->service()->getNaniteCost($steps));
    }

    public function testProductionSettingsOverrideDefaultsAndAreReadFresh(): void
    {
        $recipe = new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::MODULE_OPTIMIZATION);
        $recipes = $this->mock(TechnologyRepositoryInterface::class);
        $recipes->shouldReceive('getRecipes')->andReturn([$recipe]);
        $service = new ModuleProductionQuality(new ModuleOptimizationProgress($this->mock(UserModuleOptimizationRepositoryInterface::class)), $recipes);
        self::assertSame(1, $service->getNaniteCost(7));

        $recipe->setSettings(['production_nanite_free_steps' => 1, 'production_nanite_step_size' => 5, 'production_nanite_amount' => 3]);
        self::assertSame(6, $service->getNaniteCost(7));
        self::assertSame(3, $service->getNaniteCost(6));
        self::assertSame(0, $service->getNaniteCost(1));

        $recipe->setSettings(['production_nanite_free_steps' => -1, 'production_nanite_step_size' => 0, 'production_nanite_amount' => -1]);
        self::assertSame(2, $service->getNaniteCost(15));
    }

    public function testMissingRecipeIsNotCachedAndCannotProducePaidQualityForFree(): void
    {
        $nanites = $this->commodity(76);
        $recipe = new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setNaniteCommodity($nanites);
        $recipes = $this->mock(TechnologyRepositoryInterface::class);
        $recipes->shouldReceive('getRecipes')->andReturn([], [$recipe]);
        $service = new ModuleProductionQuality(new ModuleOptimizationProgress($this->mock(UserModuleOptimizationRepositoryInterface::class)), $recipes);
        self::assertNull($service->getNaniteCommodity());
        self::assertSame($nanites, $service->getNaniteCommodity());

        $this->expectException(LogicException::class);
        $this->service()->getCosts(new Module(), 5);
    }

    public function testCostsAreMergedWithoutMutatingPersistedModuleCosts(): void
    {
        $nanites = $this->commodity(76);
        $metal = $this->commodity(1);
        $recipe = new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setNaniteCommodity($nanites);
        $module = new Module();
        $metalCost = new ModuleCost()->setCommodity($metal)->setAmount(10);
        $naniteCost = new ModuleCost()->setCommodity($nanites)->setAmount(3);
        $duplicateMetal = new ModuleCost()->setCommodity($metal)->setAmount(5);
        $module->getCost()->add($metalCost);
        $module->getCost()->add($naniteCost);
        $module->getCost()->add($duplicateMetal);

        $costs = $this->service($recipe)->getCosts($module, 15);

        self::assertSame(15, $costs[1]->getAmount());
        self::assertSame(5, $costs[76]->getAmount());
        self::assertSame($metal, $costs[1]->getCommodity());
        self::assertSame($nanites, $costs[76]->getCommodity());
        self::assertNotSame($metalCost, $costs[1]);
        self::assertNotSame($naniteCost, $costs[76]);
        self::assertSame(10, $metalCost->getAmount());
        self::assertSame(3, $naniteCost->getAmount());
        self::assertSame(5, $duplicateMetal->getAmount());
        self::assertCount(3, $module->getCost());
    }

    public function testOnlyStoredAccountProgressDeterminesTheAvailableQuality(): void
    {
        $user = new User();
        $module = new Module();
        $progress = $this->mock(UserModuleOptimizationRepositoryInterface::class);
        $progress->shouldReceive('get')->with($user, $module)->once()->andReturn(new UserModuleOptimization()->setImprovementSteps(17));
        $service = new ModuleProductionQuality(new ModuleOptimizationProgress($progress), $this->mock(TechnologyRepositoryInterface::class));

        self::assertSame(17, $service->getImprovementSteps($user, $module));
    }

    public function testInvalidQualityIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service()->getNaniteCost(71);
    }

    private function service(?TechnologyRecipe $recipe = null): ModuleProductionQuality
    {
        $recipes = $this->mock(TechnologyRepositoryInterface::class);
        $recipes->shouldReceive('getRecipes')->andReturn($recipe === null ? [] : [$recipe]);
        return new ModuleProductionQuality(new ModuleOptimizationProgress($this->mock(UserModuleOptimizationRepositoryInterface::class)), $recipes);
    }

    private function commodity(int $id): Commodity
    {
        $commodity = new Commodity();
        new ReflectionProperty(Commodity::class, 'id')->setValue($commodity, $id);
        return $commodity;
    }
}
