<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CreateModules;

use Doctrine\Common\Collections\ArrayCollection;
use request;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Research\ModuleOptimizationProgress;
use Stu\Component\Research\ModuleProductionQuality;
use Stu\Component\Research\TechnologyRecipeKindEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleBuildingFunction;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\ModuleQueue;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\UserModuleOptimization;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\UserModuleOptimizationRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;
use Stu\StuTestCase;

class CreateModulesTest extends StuTestCase
{
    public function testHandleProjectsMissingCommoditiesAcrossMultipleFailedModules(): void
    {
        $colonyId = 42;
        $userId = 7;
        $commodityId = 1;
        $function = BuildingFunctionEnum::MODULEFAB_TYPE1_LVL3;
        $informationWrapper = new InformationWrapper();

        request::setMockVars([
            'id' => $colonyId,
            'func' => $function->value,
            'moduleids' => [101, 202],
            'values' => [1, 1]
        ]);

        $colonyLoader = $this->mock(ColonyLoaderInterface::class);
        $moduleBuildingFunctionRepository = $this->mock(ModuleBuildingFunctionRepositoryInterface::class);
        $moduleQueueRepository = $this->mock(ModuleQueueRepositoryInterface::class);
        $planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $storageManager = $this->mock(StorageManagerInterface::class);
        $colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $context = $this->mock(ActionControllerContext::class);
        $user = $this->mock(User::class);
        $colony = $this->mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);
        $storedMaterial = $this->mock(Storage::class);
        $material = $this->mock(Commodity::class);
        $moduleBuildingFunction1 = $this->mock(ModuleBuildingFunction::class);
        $moduleBuildingFunction2 = $this->mock(ModuleBuildingFunction::class);
        $module1 = $this->mock(Module::class);
        $module2 = $this->mock(Module::class);
        $cost1 = $this->mock(ModuleCost::class);
        $cost2 = $this->mock(ModuleCost::class);

        $context->shouldReceive('setView')
            ->with(ShowColony::VIEW_IDENTIFIER)
            ->once();
        $context->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $context->shouldReceive('getInfo')
            ->withNoArgs()
            ->andReturn($informationWrapper);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($userId);

        $colonyLoader->shouldReceive('loadWithOwnerValidation')
            ->with($colonyId, $userId)
            ->once()
            ->andReturn($colony);

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($colonyId);
        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$commodityId => $storedMaterial]));
        $colony->shouldReceive('getChangeable')
            ->withNoArgs()
            ->andReturn($changeable);

        $changeable->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(999);
        $changeable->shouldNotReceive('lowerEps');

        $storedMaterial->shouldReceive('getCommodity')->andReturn($material);
        $storedMaterial->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->andReturn($commodityId);
        $storedMaterial->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn(10);

        $material->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($commodityId);
        $material->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('Baumaterial');

        $planetFieldRepository->shouldReceive('getCountByColonyAndBuildingFunctionAndState')
            ->with($colony, [$function], [0, 1])
            ->once()
            ->andReturn(1);

        $moduleBuildingFunctionRepository->shouldReceive('getByBuildingFunctionAndUser')
            ->with($function, $userId)
            ->once()
            ->andReturn([$moduleBuildingFunction1, $moduleBuildingFunction2]);

        $moduleBuildingFunction1->shouldReceive('getModuleId')
            ->withNoArgs()
            ->andReturn(101);
        $moduleBuildingFunction1->shouldReceive('getModule')
            ->withNoArgs()
            ->andReturn($module1);
        $moduleBuildingFunction2->shouldReceive('getModuleId')
            ->withNoArgs()
            ->andReturn(202);
        $moduleBuildingFunction2->shouldReceive('getModule')
            ->withNoArgs()
            ->andReturn($module2);

        $module1->shouldReceive('getEcost')
            ->withNoArgs()
            ->andReturn(0);
        $module1->shouldReceive('getCost')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$cost1]));
        $module1->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('Klingonische Torpedobank (Klasse 6)');
        $module2->shouldReceive('getEcost')
            ->withNoArgs()
            ->andReturn(0);
        $module2->shouldReceive('getCost')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$cost2]));
        $module2->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('Mehrfach-Torpedogeschütz (Klasse 6)');

        $cost1->shouldReceive('getCommodity')
            ->withNoArgs()
            ->andReturn($material);
        $cost1->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn(120);
        $cost2->shouldReceive('getCommodity')
            ->withNoArgs()
            ->andReturn($material);
        $cost2->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn(110);

        $storageManager->shouldNotReceive('lowerStorage');
        $colonyRepository->shouldNotReceive('save');
        $moduleQueueRepository->shouldNotReceive('getByColonyAndModuleAndBuilding');
        $moduleQueueRepository->shouldNotReceive('prototype');
        $moduleQueueRepository->shouldNotReceive('save');

        $technology = $this->mock(TechnologyRepositoryInterface::class);
        $technology->shouldReceive('getRecipes')->andReturn([]);
        $subject = new CreateModules(
            $colonyLoader,
            $moduleBuildingFunctionRepository,
            $moduleQueueRepository,
            $planetFieldRepository,
            $storageManager,
            $colonyRepository,
            new ModuleProductionQuality(new ModuleOptimizationProgress($this->mock(UserModuleOptimizationRepositoryInterface::class)), $technology)
        );

        $subject->handle($context);

        $this->assertSame(
            [
                '0 von 1 Klingonische Torpedobank (Klasse 6)',
                "\u{00A0}\u{00A0}\u{00A0}\u{00A0}- Zur Herstellung von weiteren 1 Klingonische Torpedobank (Klasse 6) werden benötigt: 110 Baumaterial",
                '0 von 1 Mehrfach-Torpedogeschütz (Klasse 6)',
                "\u{00A0}\u{00A0}\u{00A0}\u{00A0}- Zur Herstellung von weiteren 1 Mehrfach-Torpedogeschütz (Klasse 6) werden benötigt: 110 Baumaterial",
                "\u{00A0}\u{00A0}\u{00A0}\u{00A0}- Insgesamt werden zusätzlich benötigt: 220 Baumaterial"
            ],
            $informationWrapper->getInformations()
        );
    }
    /** @return iterable<string, array{?string, int, int, int, int, int, int, bool}> */
    public static function productionSelections(): iterable
    {
        yield 'missing selection remains standard despite account bonus' => [null, 17, 0, 100, 100, 3, 0, false];
        yield 'explicit standard costs no nanites' => ['0', 17, 0, 100, 100, 3, 0, false];
        yield 'selected current bonus snapshots queue and charges per unit' => ['1', 17, 6, 100, 100, 3, 6, false];
        yield 'missing nanites prevents all production' => ['1', 17, 0, 100, 100, 0, 0, false];
        yield 'nanites limit partial production' => ['1', 17, 3, 100, 100, 1, 2, false];
        yield 'remaining nanite reduces projected deficit' => ['1', 17, 5, 100, 100, 2, 4, false];
        yield 'first four steps need no nanites' => ['1', 4, 0, 100, 100, 3, 0, false];
        yield 'step five costs one nanite per unit' => ['1', 5, 3, 100, 100, 3, 3, false];
        yield 'client cannot submit an arbitrary percentage' => ['70', 17, 0, 100, 100, 3, 0, false];
        yield 'matching quality queue grows independently' => ['1', 17, 6, 100, 100, 3, 6, true];
        yield 'ordinary materials limit optimized production' => ['1', 17, 100, 3, 100, 1, 2, false];
        yield 'energy limits optimized production' => ['1', 17, 100, 100, 3, 1, 2, false];
    }

    #[DataProvider('productionSelections')]
    public function testProductionUsesOnlyServerSelectedQualityAndIncludesNanitesInPartialProduction(?string $selection, int $accountSteps, int $nanitesAvailable, int $materialsAvailable, int $energyAvailable, int $produced, int $nanitesUsed, bool $existingQueue): void
    {
        $function = BuildingFunctionEnum::MODULEFAB_TYPE1_LVL3;
        $request = ['id' => 42, 'func' => $function->value, 'moduleids' => [10731], 'values' => [3]];
        if ($selection !== null) {
            $request['optimizations'] = [10731 => $selection];
        }
        request::setMockVars($request);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(7);
        $colony = $this->mock(Colony::class);
        $colony->shouldReceive('getId')->andReturn(42);
        $changeable = new ColonyChangeable($colony)->setEps($energyAvailable);
        $colony->shouldReceive('getChangeable')->andReturn($changeable);
        $metal = $this->productionCommodity(1, 'Baumaterial');
        $nanites = $this->productionCommodity(76, 'Borg-Naniten');
        $stock = new ArrayCollection([
            1 => $this->productionStock($metal, $materialsAvailable),
            76 => $this->productionStock($nanites, $nanitesAvailable)
        ]);
        $colony->shouldReceive('getStorage')->andReturn($stock);
        $module = new Module()->setName('Disruptor')->setEcost(2);
        new ReflectionProperty(Module::class, 'id')->setValue($module, 10731);
        $module->getCost()->add(new ModuleCost()->setCommodity($metal)->setAmount(2));
        $mapping = $this->mock(ModuleBuildingFunction::class);
        $mapping->shouldReceive('getModuleId')->andReturn(10731);
        $mapping->shouldReceive('getModule')->andReturn($module);
        $loader = $this->mock(ColonyLoaderInterface::class);
        $loader->shouldReceive('loadWithOwnerValidation')->with(42, 7)->once()->andReturn($colony);
        $moduleFunctions = $this->mock(ModuleBuildingFunctionRepositoryInterface::class);
        $moduleFunctions->shouldReceive('getByBuildingFunctionAndUser')->with($function, 7)->once()->andReturn([$mapping]);
        $fields = $this->mock(PlanetFieldRepositoryInterface::class);
        $fields->shouldReceive('getCountByColonyAndBuildingFunctionAndState')->with($colony, [$function], [0, 1])->once()->andReturn(1);
        $progress = new UserModuleOptimization()->setImprovementSteps($accountSteps);
        $progressRepository = $this->mock(UserModuleOptimizationRepositoryInterface::class);
        if ($selection === '1') {
            $progressRepository->shouldReceive('get')->with($user, $module)->once()->andReturn($progress);
        } else {
            $progressRepository->shouldNotReceive('get');
        }
        $recipes = $this->mock(TechnologyRepositoryInterface::class);
        $recipes->shouldReceive('getRecipes')->andReturn([new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setNaniteCommodity($nanites)]);
        $quality = new ModuleProductionQuality(new ModuleOptimizationProgress($progressRepository), $recipes);
        $queues = $this->mock(ModuleQueueRepositoryInterface::class);
        $queue = new ModuleQueue();
        $selectedSteps = $selection === '1' ? $accountSteps : 0;
        if ($existingQueue) {
            $queue->setAmount(2)->setImprovementSteps($selectedSteps);
        }
        $colonies = $this->mock(ColonyRepositoryInterface::class);
        $storage = $this->mock(StorageManagerInterface::class);
        if ($produced > 0) {
            $queues->shouldReceive('getByColonyAndModuleAndBuilding')->with(42, 10731, $function->value, $selectedSteps)->once()->andReturn($existingQueue ? $queue : null);
            if (!$existingQueue) {
                $queues->shouldReceive('prototype')->once()->andReturn($queue);
            }
            $queues->shouldReceive('save')->with($queue)->once();
            $colonies->shouldReceive('save')->with($colony)->once();
            $storage->shouldReceive('lowerStorage')->times($nanitesUsed > 0 ? 2 : 1)->andReturnUsing(static function (Colony $host, Commodity $commodity, int $amount) use ($stock, $colony): void {
                self::assertSame($colony, $host);
                $stored = $stock->get($commodity->getId());
                self::assertNotNull($stored);
                $stored->removeAmount($amount);
            });
        } else {
            $queues->shouldNotReceive('getByColonyAndModuleAndBuilding');
            $storage->shouldNotReceive('lowerStorage');
        }
        $information = new InformationWrapper();
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->with(ShowColony::VIEW_IDENTIFIER)->once();
        $context->shouldReceive('getUser')->andReturn($user);
        $context->shouldReceive('getInfo')->andReturn($information);

        new CreateModules($loader, $moduleFunctions, $queues, $fields, $storage, $colonies, $quality)->handle($context);

        $metalStock = $stock->get(1);
        $naniteStock = $stock->get(76);
        self::assertNotNull($metalStock);
        self::assertNotNull($naniteStock);
        self::assertSame($materialsAvailable - $produced * 2, $metalStock->getAmount());
        self::assertSame($nanitesAvailable - $nanitesUsed, $naniteStock->getAmount());
        self::assertSame($energyAvailable - $produced * 2, $changeable->getEps());
        self::assertSame($produced + ($existingQueue ? 2 : 0), $queue->getAmount());
        if ($produced > 0) {
            $progress->setImprovementSteps(70);
            self::assertSame($selectedSteps, $queue->getImprovementSteps());
        }
        if ($selection === '1' && $accountSteps === 17 && $nanitesAvailable < 6) {
            $deficit = (3 - $produced) * 2 - ($nanitesAvailable - $nanitesUsed);
            self::assertStringContainsString($deficit . ' Borg-Naniten', implode(' ', $information->getInformations()));
        }
    }

    private function productionCommodity(int $id, string $name): Commodity
    {
        $commodity = new Commodity()->setName($name);
        new ReflectionProperty(Commodity::class, 'id')->setValue($commodity, $id);
        return $commodity;
    }

    private function productionStock(Commodity $commodity, int $amount): Storage
    {
        $storage = new Storage()->setCommodity($commodity)->setAmount($amount);
        return $storage;
    }
}
