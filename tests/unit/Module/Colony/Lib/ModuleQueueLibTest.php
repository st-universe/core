<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Research\ModuleOptimizationProgress;
use Stu\Component\Research\ModuleProductionQuality;
use Stu\Component\Research\TechnologyRecipeKindEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\ModuleQueue;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;
use Stu\Orm\Repository\UserModuleOptimizationRepositoryInterface;
use Stu\StuTestCase;

final class ModuleQueueLibTest extends StuTestCase
{
    /** @return iterable<string, array{int, int}> */
    public static function qualities(): iterable
    {
        yield 'standard' => [0, 0];
        yield 'free improvement' => [4, 0];
        yield 'first nanite tier' => [5, 1];
        yield 'second nanite tier' => [15, 2];
        yield 'maximum improvement' => [70, 7];
    }

    #[DataProvider('qualities')]
    public function testCancellationRefundsActualQueueQuality(int $steps, int $nanitesPerModule): void
    {
        $colony = $this->colony(1000);
        $material = $this->commodity(3);
        $nanites = $this->commodity(76);
        $module = new Module();
        $baseCost = new ModuleCost()->setCommodity($material)->setAmount(3);
        $module->getCost()->add($baseCost);
        $queue = new ModuleQueue()->setModule($module)->setAmount(2)->setImprovementSteps($steps);
        $repository = $this->mock(ModuleQueueRepositoryInterface::class);
        $repository->shouldReceive('getByColonyAndBuilding')->with(42, [BuildingFunctionEnum::TECH_CENTER->value])->once()->andReturn([$queue]);
        $repository->shouldReceive('delete')->with($queue)->once();
        $refunds = [];
        $subject = new ModuleQueueLib($repository, $this->storage($refunds), $this->quality($nanites));
        $subject->cancelModuleQueues($colony, BuildingFunctionEnum::TECH_CENTER);
        $expected = [3 => 6];
        if ($nanitesPerModule > 0) {
            $expected[76] = $nanitesPerModule * 2;
        }
        self::assertSame($expected, $refunds);
        self::assertSame(3, $baseCost->getAmount());
    }

    public function testBuildingRemovalKeepsRefundWithinStorageLimit(): void
    {
        $colony = $this->colony(7);
        $module = new Module();
        $module->getCost()->add(new ModuleCost()->setCommodity($this->commodity(3))->setAmount(3));
        $queue = new ModuleQueue()->setModule($module)->setAmount(2)->setImprovementSteps(15);
        $building = new Building();
        $building->getFunctions()->set(BuildingFunctionEnum::TECH_CENTER->value, new BuildingFunction()->setFunction(BuildingFunctionEnum::TECH_CENTER));
        $repository = $this->mock(ModuleQueueRepositoryInterface::class);
        $repository->shouldReceive('getByColonyAndBuilding')->with(42, [BuildingFunctionEnum::TECH_CENTER->value])->once()->andReturn([$queue]);
        $repository->shouldReceive('delete')->with($queue)->once();
        $refunds = [];
        new ModuleQueueLib($repository, $this->storage($refunds), $this->quality($this->commodity(76)))
            ->cancelModuleQueuesForBuilding($colony, $building);
        self::assertSame([3 => 6, 76 => 1], $refunds);
        self::assertSame(7, $colony->getStorageSum());
    }

    private function colony(int $capacity): Colony
    {
        $colony = new Colony();
        $colony->setChangeable(new ColonyChangeable($colony)->setMaxStorage($capacity));
        new ReflectionProperty($colony, 'id')->setValue($colony, 42);
        return $colony;
    }

    private function commodity(int $id): Commodity
    {
        $commodity = new Commodity();
        new ReflectionProperty($commodity, 'id')->setValue($commodity, $id);
        return $commodity;
    }

    private function quality(Commodity $nanites): ModuleProductionQuality
    {
        $recipe = new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setNaniteCommodity($nanites)
            ->setSettings(['production_nanite_free_steps' => 4, 'production_nanite_step_size' => 10, 'production_nanite_amount' => 1]);
        $recipes = $this->mock(TechnologyRepositoryInterface::class);
        $recipes->shouldReceive('getRecipes')->once()->andReturn([$recipe]);
        return new ModuleProductionQuality(new ModuleOptimizationProgress($this->mock(UserModuleOptimizationRepositoryInterface::class)), $recipes);
    }

    /** @param array<int, int> $refunds */
    private function storage(array &$refunds): StorageManagerInterface
    {
        $manager = $this->mock(StorageManagerInterface::class);
        $manager->shouldReceive('upperStorage')->andReturnUsing(static function (Colony $colony, Commodity $commodity, int $amount) use (&$refunds): void {
            $id = $commodity->getId();
            $refunds[$id] = ($refunds[$id] ?? 0) + $amount;
            $stored = $colony->getStorage()->get($id) ?? new Storage()->setCommodity($commodity);
            $stored->setAmount($stored->getAmount() + $amount);
            $colony->getStorage()->set($id, $stored);
        });
        return $manager;
    }
}
