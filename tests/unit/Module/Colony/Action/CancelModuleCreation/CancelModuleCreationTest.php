<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CancelModuleCreation;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Research\ModuleOptimizationProgress;
use Stu\Component\Research\ModuleProductionQuality;
use Stu\Component\Research\TechnologyRecipeKindEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\ModuleQueue;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;
use Stu\Orm\Repository\UserModuleOptimizationRepositoryInterface;

final class CancelModuleCreationTest extends ActionControllerTestCase
{
    /** @return iterable<string, array{int, int, int, int, int, bool, int}> */
    public static function cancellations(): iterable
    {
        yield 'standard only' => [2, 6, 0, 2, 2, false, 1000];
        yield 'standard then first improved tier' => [3, 9, 1, 1, 2, false, 1000];
        yield 'across two improved tiers' => [5, 15, 4, 2, 1, true, 1000];
        yield 'storage limit' => [5, 10, 0, 2, 1, true, 10];
        yield 'request above total' => [99, 18, 6, 2, 2, true, 1000];
    }

    #[DataProvider('cancellations')]
    public function testAggregatedCancellationUsesEachQueueQualityWithoutExcessRefund(int $count, int $materialRefund, int $naniteRefund, int $firstRemaining, int $secondRemaining, bool $firstDeleted, int $capacity): void
    {
        $colony = new Colony();
        $colony->setChangeable(new ColonyChangeable($colony)->setMaxStorage($capacity)->setMaxEps(10)->setEps(0));
        $user = new User();
        $module = new Module()->setEcost(2);
        $this->assign($colony, 'id', 42);
        $this->assign($user, 'id', 261);
        $this->assign($module, 'id', 10601);
        $material = $this->commodity(3);
        $nanites = $this->commodity(76);
        $module->getCost()->add(new ModuleCost()->setCommodity($material)->setAmount(3));
        $standard = new ModuleQueue()->setModule($module)->setAmount(2)->setImprovementSteps(0);
        $first = new ModuleQueue()->setModule($module)->setAmount(2)->setImprovementSteps(5);
        $second = new ModuleQueue()->setModule($module)->setAmount(2)->setImprovementSteps(15);
        $foreignModule = new Module();
        $this->assign($foreignModule, 'id', 10901);
        $foreign = new ModuleQueue()->setModule($foreignModule)->setAmount(100)->setImprovementSteps(70);
        $function = BuildingFunctionEnum::TECH_CENTER;
        request::setMockVars(['id' => 42, 'module' => 10601, 'func' => $function->value, 'count' => $count]);
        $loader = $this->mock(ColonyLoaderInterface::class);
        $loader->shouldReceive('loadWithOwnerValidation')->with(42, 261)->once()->andReturn($colony);
        $modules = $this->mock(ModuleRepositoryInterface::class);
        $modules->shouldReceive('find')->with(10601)->once()->andReturn($module);
        $fields = $this->mock(PlanetFieldRepositoryInterface::class);
        $fields->shouldReceive('getCountByColonyAndBuildingFunctionAndState')->with($colony, [$function], [0, 1])->once()->andReturn(1);
        $queues = $this->mock(ModuleQueueRepositoryInterface::class);
        $queues->shouldReceive('getByColonyAndBuilding')->with(42, [$function->value])->once()->andReturn([$second, $foreign, $first, $standard]);
        $queues->shouldReceive('delete')->with($standard)->once();
        if ($firstDeleted) {
            $queues->shouldReceive('delete')->with($first)->once();
        } elseif ($count > 2) {
            $queues->shouldReceive('save')->with($first)->once();
        }
        if ($count >= 6) {
            $queues->shouldReceive('delete')->with($second)->once();
        } elseif ($count > 4) {
            $queues->shouldReceive('save')->with($second)->once();
        }
        $colonies = $this->mock(ColonyRepositoryInterface::class);
        $colonies->shouldReceive('save')->with($colony)->once();
        $refunds = [];
        $manager = $this->mock(StorageManagerInterface::class);
        $manager->shouldReceive('upperStorage')->andReturnUsing(static function (Colony $host, Commodity $commodity, int $amount) use (&$refunds): void {
            $id = $commodity->getId();
            $refunds[$id] = ($refunds[$id] ?? 0) + $amount;
            $stored = $host->getStorage()->get($id) ?? new Storage()->setCommodity($commodity);
            $stored->setAmount($stored->getAmount() + $amount);
            $host->getStorage()->set($id, $stored);
        });
        $recipe = new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setNaniteCommodity($nanites)
            ->setSettings(['production_nanite_free_steps' => 4, 'production_nanite_step_size' => 10, 'production_nanite_amount' => 1]);
        $recipes = $this->mock(TechnologyRepositoryInterface::class);
        $recipes->shouldReceive('getRecipes')->andReturn([$recipe]);
        $quality = new ModuleProductionQuality(new ModuleOptimizationProgress($this->mock(UserModuleOptimizationRepositoryInterface::class)), $recipes);
        $this->game->shouldReceive('getUser')->andReturn($user);
        $this->game->shouldReceive('setView')->once();
        $this->game->shouldReceive('setViewContext')->once();
        new CancelModuleCreation($loader, $queues, $modules, $fields, $manager, $colonies, $quality)->handle($this->game);
        self::assertSame(0, $standard->getModuleId());
        self::assertLessThanOrEqual($capacity, $colony->getStorageSum());
        self::assertSame($materialRefund, $refunds[3]);
        self::assertSame($naniteRefund, $refunds[76] ?? 0);
        self::assertSame(min(10, min($count, 6) * 2), $colony->getChangeable()->getEps());
        self::assertSame($firstRemaining, $first->getAmount());
        self::assertSame($secondRemaining, $second->getAmount());
        self::assertSame(100, $foreign->getAmount());
    }

    private function commodity(int $id): Commodity
    {
        $commodity = new Commodity();
        $this->assign($commodity, 'id', $id);
        return $commodity;
    }

    private function assign(object $entity, string $field, mixed $value): void
    {
        new ReflectionProperty($entity, $field)->setValue($entity, $value);
    }
}
