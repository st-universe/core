<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Research\ModuleOptimizationProgress;
use Stu\Component\Research\ModuleProductionQuality;
use Stu\Component\Research\TechnologyRecipeKindEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserModuleOptimization;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;
use Stu\Orm\Repository\UserModuleOptimizationRepositoryInterface;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class ModuleFabricationListItemTest extends StuTestCase
{
    public function testUnresearchedModuleHasOnlyStandardProductionWithoutAnEmptySelector(): void
    {
        $item = $this->item(0);
        $variants = $item->getProductionVariants();
        self::assertCount(1, $variants);
        self::assertFalse($variants[0]['optimized']);
        $html = $this->render($item);
        self::assertStringNotContainsString('<select', $html);
        self::assertStringNotContainsString('Borg-Naniten', $html);
        self::assertStringContainsString('data-optimized="0"', $html);
    }

    /** @return iterable<string, array{int}> */
    public static function freeBonuses(): iterable
    {
        yield 'first improvement' => [1];
        yield 'last free improvement' => [4];
    }

    #[DataProvider('freeBonuses')]
    public function testSmallBonusIsSelectableWithoutAnyNaniteCostRow(int $steps): void
    {
        $item = $this->item($steps);
        $variants = $item->getProductionVariants();
        self::assertCount(2, $variants);
        self::assertTrue($variants[1]['optimized']);
        self::assertCount(1, $variants[1]['costs']);
        $html = $this->render($item);
        self::assertStringContainsString('name="optimizations[42]"', $html);
        self::assertStringContainsString('Standard (+0 %)', $html);
        self::assertStringContainsString('Fertigungsbonus (+0,' . $steps . ' %)', $html);
        self::assertStringNotContainsString('Borg-Naniten', $html);
        self::assertStringNotContainsString('/assets/commodities/76.png', $html);
    }

    /** @return iterable<string, array{int, int}> */
    public static function paidBonuses(): iterable
    {
        yield 'first nanite' => [5, 1];
        yield 'last one nanite' => [14, 1];
        yield 'second nanite' => [15, 2];
        yield 'last two nanites' => [24, 2];
        yield 'seven nanites' => [65, 7];
    }

    #[DataProvider('paidBonuses')]
    public function testBonusCostsMatchTheServerWhileStandardStaysUnchanged(int $steps, int $nanites): void
    {
        $item = $this->item($steps);
        $variants = $item->getProductionVariants();
        self::assertCount(1, $variants[0]['costs']);
        self::assertSame(10, $variants[0]['costs'][0]->getAmount());
        self::assertSame($nanites, $variants[1]['costs'][76]->getAmount());
        self::assertSame(10, $variants[1]['costs'][2]->getAmount());
        $html = $this->render($item);
        self::assertStringContainsString('data-commodity-name="Borg-Naniten"', $html);
        self::assertStringContainsString('data-cost="' . $nanites . '"', $html);
        self::assertStringContainsString('data-optimized="1"', $html);
        self::assertStringContainsString("style=\"display: none; flex-wrap: wrap; gap: 8px;\"", $html);
    }

    private function render(ModuleFabricationListItem $item): string
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__, 6) . '/src'), ['strict_variables' => true]);
        return $twig->render('html/colony/menu/moduleProductionChoices.twig', ['data' => $item, 'moduleId' => 42]);
    }

    private function item(int $steps): ModuleFabricationListItem
    {
        $user = new User();
        $colony = new Colony()->setUser($user);
        $module = new Module()->setName('Sensoren')->setEcost(3);
        $material = $this->commodity(2, 'Duranium');
        $nanites = $this->commodity(76, 'Borg-Naniten');
        $module->getCost()->add(new ModuleCost()->setCommodity($material)->setAmount(10));
        $progress = $this->mock(UserModuleOptimizationRepositoryInterface::class);
        $progress->shouldReceive('get')->with($user, $module)->andReturn($steps === 0 ? null : new UserModuleOptimization()->setImprovementSteps($steps));
        $recipe = new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setNaniteCommodity($nanites)->setSettings([
            'production_nanite_free_steps' => 4, 'production_nanite_step_size' => 10, 'production_nanite_amount' => 1
        ]);
        $recipes = $this->mock(TechnologyRepositoryInterface::class);
        $recipes->shouldReceive('getRecipes')->andReturn([$recipe]);
        $quality = new ModuleProductionQuality(new ModuleOptimizationProgress($progress), $recipes);
        return new ModuleFabricationListItem($this->mock(ModuleQueueRepositoryInterface::class), $module, $colony, productionQuality: $quality);
    }

    private function commodity(int $id, string $name): Commodity
    {
        $commodity = $this->mock(Commodity::class);
        $commodity->shouldReceive('getId', 'getSort')->andReturn($id);
        $commodity->shouldReceive('getName')->andReturn($name);
        return $commodity;
    }
}
