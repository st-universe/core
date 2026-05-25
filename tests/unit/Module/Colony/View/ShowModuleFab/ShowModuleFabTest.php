<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use InvalidArgumentException;
use Mockery\MockInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\BuildplanHangar;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ShipRumpCost;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Repository\BuildplanHangarRepositoryInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCostRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\StuTestCase;

class ShowModuleFabTest extends StuTestCase
{
    private MockInterface&ColonyLoaderInterface $colonyLoader;

    private MockInterface&ShowModuleFabRequestInterface $showModuleFabRequest;

    private MockInterface&ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository;

    private MockInterface&BuildingFunctionRepositoryInterface $buildingFunctionRepository;

    private MockInterface&ModuleQueueRepositoryInterface $moduleQueueRepository;

    private MockInterface&SpacecraftRumpRepositoryInterface $spacecraftRumpRepository;

    private MockInterface&ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    private MockInterface&SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository;

    private MockInterface&ModuleRepositoryInterface $moduleRepository;

    private MockInterface&BuildplanHangarRepositoryInterface $buildplanHangarRepository;

    private MockInterface&ShipRumpCostRepositoryInterface $shipRumpCostRepository;

    private ShowModuleFab $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->colonyLoader = $this->mock(ColonyLoaderInterface::class);
        $this->showModuleFabRequest = $this->mock(ShowModuleFabRequestInterface::class);
        $this->moduleBuildingFunctionRepository = $this->mock(ModuleBuildingFunctionRepositoryInterface::class);
        $this->buildingFunctionRepository = $this->mock(BuildingFunctionRepositoryInterface::class);
        $this->moduleQueueRepository = $this->mock(ModuleQueueRepositoryInterface::class);
        $this->spacecraftRumpRepository = $this->mock(SpacecraftRumpRepositoryInterface::class);
        $this->shipRumpModuleLevelRepository = $this->mock(ShipRumpModuleLevelRepositoryInterface::class);
        $this->spacecraftBuildplanRepository = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $this->moduleRepository = $this->mock(ModuleRepositoryInterface::class);
        $this->buildplanHangarRepository = $this->mock(BuildplanHangarRepositoryInterface::class);
        $this->shipRumpCostRepository = $this->mock(ShipRumpCostRepositoryInterface::class);

        $this->subject = new ShowModuleFab(
            $this->colonyLoader,
            $this->showModuleFabRequest,
            $this->moduleBuildingFunctionRepository,
            $this->buildingFunctionRepository,
            $this->moduleQueueRepository,
            $this->spacecraftRumpRepository,
            $this->shipRumpModuleLevelRepository,
            $this->spacecraftBuildplanRepository,
            $this->moduleRepository,
            $this->buildplanHangarRepository,
            $this->shipRumpCostRepository
        );
    }

    public function testSetRumpModulesUsesRepositoryFilteredModules(): void
    {
        $colony = $this->mock(Colony::class);
        $rump = $this->mock(SpacecraftRump::class);
        $moduleLevels = $this->mock(ShipRumpModuleLevel::class);
        $roleSpecificModule = $this->mock(Module::class);
        $genericModule = $this->mock(Module::class);

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(77);

        $rump->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(5);
        $rump->shouldReceive('getRoleId')
            ->withNoArgs()
            ->andReturn(SpacecraftRumpRoleEnum::PHASER_SHIP);

        $this->shipRumpModuleLevelRepository->shouldReceive('getByShipRump')
            ->with($rump)
            ->once()
            ->andReturn($moduleLevels);

        $moduleLevels->shouldReceive('getMinimumLevel')
            ->withAnyArgs()
            ->andReturn(1);
        $moduleLevels->shouldReceive('getMaximumLevel')
            ->withAnyArgs()
            ->andReturn(3);

        $roleSpecificModule->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1001);
        $roleSpecificModule->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(SpacecraftModuleTypeEnum::PHASER);
        $roleSpecificModule->shouldReceive('getLevel')
            ->withNoArgs()
            ->andReturn(2);

        $genericModule->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(SpacecraftModuleTypeEnum::TORPEDO);
        $genericModule->shouldReceive('getLevel')
            ->withNoArgs()
            ->andReturn(2);

        $this->moduleRepository->shouldReceive('getByTypeColonyAndLevel')
            ->withAnyArgs()
            ->andReturnUsing(
                static function (
                    int $colonyId,
                    SpacecraftModuleTypeEnum $moduleType,
                    SpacecraftRumpRoleEnum $shipRumpRole,
                    array $levels
                ) use ($roleSpecificModule): array {
                    if (
                        $colonyId === 77
                        && $moduleType === SpacecraftModuleTypeEnum::PHASER
                        && $shipRumpRole === SpacecraftRumpRoleEnum::PHASER_SHIP
                        && $levels === [1, 2, 3]
                    ) {
                        return [$roleSpecificModule];
                    }

                    return [];
                }
            );

        $this->moduleRepository->shouldReceive('getBySpecialTypeAndRump')
            ->with($colony, SpacecraftModuleTypeEnum::SPECIAL, 5)
            ->once()
            ->andReturn([]);

        $roleSpecificItem = new ModuleFabricationListItem(
            $this->moduleQueueRepository,
            $roleSpecificModule,
            $colony
        );
        $genericItem = new ModuleFabricationListItem(
            $this->moduleQueueRepository,
            $genericModule,
            $colony
        );

        $allModules = [
            1001 => $roleSpecificItem,
            1002 => $genericItem
        ];

        $this->invokeSetRumpModules($colony, [$rump], $allModules);

        $this->assertStringContainsString('rump_5', $roleSpecificItem->getClass());
        $this->assertStringNotContainsString('rump_5', $genericItem->getClass());
    }

    public function testSetRumpModulesUsesHangarCostModulesOnly(): void
    {
        $colony = $this->mock(Colony::class);
        $rump = $this->mock(SpacecraftRump::class);
        $hangarCostModule = $this->mock(Module::class);
        $theoreticalModule = $this->mock(Module::class);

        $rump->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(5);

        $hangarCostModule->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1001);
        $hangarCostModule->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(SpacecraftModuleTypeEnum::PHASER);
        $hangarCostModule->shouldReceive('getLevel')
            ->withNoArgs()
            ->andReturn(2);

        $theoreticalModule->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(SpacecraftModuleTypeEnum::TORPEDO);
        $theoreticalModule->shouldReceive('getLevel')
            ->withNoArgs()
            ->andReturn(2);

        $hangarCostItem = new ModuleFabricationListItem(
            $this->moduleQueueRepository,
            $hangarCostModule,
            $colony
        );
        $theoreticalItem = new ModuleFabricationListItem(
            $this->moduleQueueRepository,
            $theoreticalModule,
            $colony
        );

        $allModules = [
            1001 => $hangarCostItem,
            1002 => $theoreticalItem
        ];

        $this->invokeSetRumpModules($colony, [$rump], $allModules, [5 => [$hangarCostModule]]);

        $this->assertStringContainsString('rump_5', $hangarCostItem->getClass());
        $this->assertStringNotContainsString('rump_5', $theoreticalItem->getClass());
    }

    public function testSetBuildplansUsesHangarCostModulesInsteadOfBuildplanModules(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $colony = $this->mock(Colony::class);
        $rump = $this->mock(SpacecraftRump::class);
        $hangar = $this->mock(BuildplanHangar::class);
        $hangarBuildplan = $this->mock(SpacecraftBuildplan::class);
        $hangarCostModule = $this->mock(Module::class);
        $buildplanOnlyModule = $this->mock(Module::class);

        $rump->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(5);

        $hangar->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->andReturn($hangarBuildplan);

        $hangarBuildplan->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(55);
        $hangarBuildplan->shouldNotReceive('getModules');

        $hangarCostModule->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1001);
        $hangarCostModule->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(SpacecraftModuleTypeEnum::PHASER);
        $hangarCostModule->shouldReceive('getLevel')
            ->withNoArgs()
            ->andReturn(2);

        $buildplanOnlyModule->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(SpacecraftModuleTypeEnum::TORPEDO);
        $buildplanOnlyModule->shouldReceive('getLevel')
            ->withNoArgs()
            ->andReturn(2);

        $game->shouldReceive('setTemplateVar')
            ->with('BUILDPLANS', [5 => [$hangarBuildplan]])
            ->once();

        $hangarCostItem = new ModuleFabricationListItem(
            $this->moduleQueueRepository,
            $hangarCostModule,
            $colony
        );
        $buildplanOnlyItem = new ModuleFabricationListItem(
            $this->moduleQueueRepository,
            $buildplanOnlyModule,
            $colony
        );

        $allModules = [
            1001 => $hangarCostItem,
            1002 => $buildplanOnlyItem
        ];

        $this->invokeSetBuildplans(
            [$rump],
            $allModules,
            $game,
            [5 => $hangar],
            [5 => [$hangarCostModule]]
        );

        $this->assertStringContainsString('buildplan_55', $hangarCostItem->getClass());
        $this->assertStringNotContainsString('buildplan_55', $buildplanOnlyItem->getClass());
    }

    public function testPrepareRumpsRemovesHangarRumpWhenCostCommodityHasNoModule(): void
    {
        $rump = $this->mock(SpacecraftRump::class);
        $hangar = $this->mock(BuildplanHangar::class);
        $cost = $this->mock(ShipRumpCost::class);

        $rump->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(5);

        $this->buildplanHangarRepository->shouldReceive('getByRump')
            ->with(5)
            ->once()
            ->andReturn($hangar);

        $this->shipRumpCostRepository->shouldReceive('getByShipRump')
            ->with(5)
            ->once()
            ->andReturn([$cost]);

        $cost->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->andReturn(42);

        $this->moduleRepository->shouldReceive('getByCommodityIds')
            ->with([42])
            ->once()
            ->andReturn([]);

        [$rumps, $hangarsByRump, $hangarModulesByRump] = $this->invokePrepareRumps([$rump]);

        $this->assertSame([], $rumps);
        $this->assertSame([], $hangarsByRump);
        $this->assertSame([], $hangarModulesByRump);
    }

    public function testPrepareRumpsKeepsHangarRumpWhenCostsResolveToModules(): void
    {
        $rump = $this->mock(SpacecraftRump::class);
        $hangar = $this->mock(BuildplanHangar::class);
        $cost = $this->mock(ShipRumpCost::class);
        $module = $this->mock(Module::class);

        $rump->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(5);

        $this->buildplanHangarRepository->shouldReceive('getByRump')
            ->with(5)
            ->once()
            ->andReturn($hangar);

        $this->shipRumpCostRepository->shouldReceive('getByShipRump')
            ->with(5)
            ->once()
            ->andReturn([$cost]);

        $cost->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->andReturn(42);

        $this->moduleRepository->shouldReceive('getByCommodityIds')
            ->with([42])
            ->once()
            ->andReturn([$module]);

        $module->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->andReturn(42);

        [$rumps, $hangarsByRump, $hangarModulesByRump] = $this->invokePrepareRumps([$rump]);

        $this->assertSame([$rump], $rumps);
        $this->assertSame([5 => $hangar], $hangarsByRump);
        $this->assertSame([5 => [$module]], $hangarModulesByRump);
    }

    public function testSetRumpModulesThrowsWithoutRole(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid rump without rump role');

        $colony = $this->mock(Colony::class);
        $rump = $this->mock(SpacecraftRump::class);

        $rump->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(5);
        $rump->shouldReceive('getRoleId')
            ->withNoArgs()
            ->andReturnNull();

        $allModules = [];

        $this->invokeSetRumpModules($colony, [$rump], $allModules);
    }

    /**
     * @param array<SpacecraftRump> $rumps
     * @param array<int, ModuleFabricationListItem> $allModules
     * @param array<int, array<int, Module>> $hangarModulesByRump
     */
    private function invokeSetRumpModules(
        Colony $colony,
        array $rumps,
        array &$allModules,
        array $hangarModulesByRump = []
    ): void {
        $callable = \Closure::bind(
            function (Colony $colony, array $rumps, array &$allModules, array $hangarModulesByRump): void {
                $this->setRumpModules($colony, $rumps, $allModules, $hangarModulesByRump);
            },
            $this->subject,
            ShowModuleFab::class
        );

        $callable($colony, $rumps, $allModules, $hangarModulesByRump);
    }

    /**
     * @param array<SpacecraftRump> $rumps
     * @param array<int, ModuleFabricationListItem> $allModules
     * @param array<int, BuildplanHangar> $hangarsByRump
     * @param array<int, array<int, Module>> $hangarModulesByRump
     */
    private function invokeSetBuildplans(
        array $rumps,
        array &$allModules,
        GameControllerInterface $game,
        array $hangarsByRump,
        array $hangarModulesByRump
    ): void {
        $callable = \Closure::bind(
            function (
                array $rumps,
                array &$allModules,
                GameControllerInterface $game,
                array $hangarsByRump,
                array $hangarModulesByRump
            ): void {
                $this->setBuildplans($rumps, $allModules, $game, $hangarsByRump, $hangarModulesByRump);
            },
            $this->subject,
            ShowModuleFab::class
        );

        $callable($rumps, $allModules, $game, $hangarsByRump, $hangarModulesByRump);
    }

    /**
     * @param array<SpacecraftRump> $rumps
     *
     * @return array<int, mixed>
     */
    private function invokePrepareRumps(array $rumps): array
    {
        $callable = \Closure::bind(
            function (array $rumps): array {
                return $this->prepareRumps($rumps);
            },
            $this->subject,
            ShowModuleFab::class
        );

        return $callable($rumps);
    }
}
