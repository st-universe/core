<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Mockery\MockInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\BuildplanHangar;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ShipRumpCost;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildplanHangarRepositoryInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCostRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
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

    private MockInterface&StorageRepositoryInterface $storageRepository;

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
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);

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
            $this->shipRumpCostRepository,
            $this->storageRepository
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

    public function testGetModuleFabRumpsAddsStationBuildplanRumps(): void
    {
        $shipRump = $this->mock(SpacecraftRump::class);
        $stationRump = $this->mock(SpacecraftRump::class);
        $stationBuildplan = $this->mock(SpacecraftBuildplan::class);

        $this->spacecraftRumpRepository->shouldReceive('getBuildableByUser')
            ->with(42)
            ->once()
            ->andReturn([5 => $shipRump]);

        $this->spacecraftBuildplanRepository->shouldReceive('getStationBuildplansByUser')
            ->with(42)
            ->once()
            ->andReturn([$stationBuildplan]);

        $stationBuildplan->shouldReceive('getRump')
            ->withNoArgs()
            ->once()
            ->andReturn($stationRump);

        $stationRump->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(12);

        $this->assertSame(
            [5 => $shipRump, 12 => $stationRump],
            $this->invokeGetModuleFabRumps(42)
        );
    }

    public function testGetAdditionalBuildplansReturnsPlansForUnavailableRumps(): void
    {
        $availableRump = $this->mock(SpacecraftRump::class);
        $availableBuildplan = $this->mock(SpacecraftBuildplan::class);
        $additionalBuildplan = $this->mock(SpacecraftBuildplan::class);

        $availableRump->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $this->spacecraftBuildplanRepository->shouldReceive('getByUser')
            ->with(42)
            ->once()
            ->andReturn([$availableBuildplan, $additionalBuildplan]);

        $availableBuildplan->shouldReceive('getRumpId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $additionalBuildplan->shouldReceive('getRumpId')
            ->withNoArgs()
            ->once()
            ->andReturn(9);
        $additionalBuildplan->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(77);

        $this->assertSame(
            [77 => $additionalBuildplan],
            $this->invokeGetAdditionalBuildplans(42, [$availableRump])
        );
    }

    public function testGetAccumulatedStorageByCommodityIdReturnsAmountIndexedByCommodity(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->storageRepository->shouldReceive('getByUserAccumulated')
            ->with($user)
            ->once()
            ->andReturn([
                ['commodity_id' => 100, 'amount' => 3],
                ['commodity_id' => 200, 'amount' => 7]
            ]);

        $this->assertSame(
            [100 => 3, 200 => 7],
            $this->invokeGetAccumulatedStorageByCommodityId($game)
        );
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

        $this->invokeSetRumpModules($colony, [$rump], $allModules, [5 => [$this->moduleRequirement($hangarCostModule, 3)]]);

        $this->assertStringContainsString('rump_5', $hangarCostItem->getClass());
        $this->assertStringNotContainsString('rump_5', $theoreticalItem->getClass());
        $this->assertSame([5 => 3], $hangarCostItem->getRequiredAmountsByRump());
    }

    public function testSetBuildplansUsesHangarCostModulesInsteadOfBuildplanModules(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $colony = $this->mock(Colony::class);
        $rump = $this->mock(SpacecraftRump::class);
        $hangarBuildplan = $this->mock(SpacecraftBuildplan::class);
        $hangarCostModule = $this->mock(Module::class);
        $buildplanOnlyModule = $this->mock(Module::class);

        $rump->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(5);

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
            [5 => $hangarBuildplan],
            [5 => [$this->moduleRequirement($hangarCostModule, 3)]]
        );

        $this->assertStringContainsString('buildplan_55', $hangarCostItem->getClass());
        $this->assertStringNotContainsString('buildplan_55', $buildplanOnlyItem->getClass());
        $this->assertSame([55 => 3], $hangarCostItem->getRequiredAmountsByBuildplan());
    }

    public function testSetBuildplansAddsAdditionalBuildplansWithoutAvailableRump(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $colony = $this->mock(Colony::class);
        $additionalBuildplan = $this->mock(SpacecraftBuildplan::class);
        $buildplanModule = $this->mock(BuildplanModule::class);
        $module = $this->mock(Module::class);

        $additionalBuildplan->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(77);
        $additionalBuildplan->shouldReceive('getModules')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$buildplanModule]));

        $buildplanModule->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($module);
        $buildplanModule->shouldReceive('getModuleCount')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $module->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1001);
        $module->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(SpacecraftModuleTypeEnum::PHASER);
        $module->shouldReceive('getLevel')
            ->withNoArgs()
            ->andReturn(2);

        $game->shouldReceive('setTemplateVar')
            ->with('BUILDPLANS', ['additional' => [77 => $additionalBuildplan]])
            ->once();

        $moduleItem = new ModuleFabricationListItem(
            $this->moduleQueueRepository,
            $module,
            $colony
        );

        $allModules = [
            1001 => $moduleItem
        ];

        $this->invokeSetBuildplans(
            [],
            $allModules,
            $game,
            [],
            [],
            [77 => $additionalBuildplan]
        );

        $this->assertStringContainsString('buildplan_77', $moduleItem->getClass());
        $this->assertSame([77 => 2], $moduleItem->getRequiredAmountsByBuildplan());
    }

    public function testPrepareRumpsRemovesHangarRumpWhenCostCommodityHasNoModule(): void
    {
        $colony = $this->mock(Colony::class);
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

        [$rumps, $buildplansByRump, $modulesByRump] = $this->invokePrepareRumps($colony, [$rump]);

        $this->assertSame([], $rumps);
        $this->assertSame([], $buildplansByRump);
        $this->assertSame([], $modulesByRump);
    }

    public function testPrepareRumpsKeepsHangarRumpWhenCostsResolveToModules(): void
    {
        $colony = $this->mock(Colony::class);
        $rump = $this->mock(SpacecraftRump::class);
        $hangar = $this->mock(BuildplanHangar::class);
        $buildplan = $this->mock(SpacecraftBuildplan::class);
        $cost = $this->mock(ShipRumpCost::class);
        $module = $this->mock(Module::class);

        $rump->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(5);

        $this->buildplanHangarRepository->shouldReceive('getByRump')
            ->with(5)
            ->once()
            ->andReturn($hangar);

        $hangar->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($buildplan);

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

        [$rumps, $buildplansByRump, $modulesByRump] = $this->invokePrepareRumps($colony, [$rump]);

        $this->assertSame([$rump], $rumps);
        $this->assertSame([5 => $buildplan], $buildplansByRump);
        $this->assertSame([5 => [$this->moduleRequirement($module)]], $modulesByRump);
    }

    public function testPrepareRumpsRemovesStationRumpWithoutStationBuildplan(): void
    {
        $colony = $this->mock(Colony::class);
        $rump = $this->mock(SpacecraftRump::class);

        $rump->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(5);
        $rump->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturnTrue();

        $this->buildplanHangarRepository->shouldReceive('getByRump')
            ->with(5)
            ->once()
            ->andReturnNull();

        $this->spacecraftBuildplanRepository->shouldReceive('getStationBuildplanByRump')
            ->with(5)
            ->once()
            ->andReturnNull();

        [$rumps, $buildplansByRump, $modulesByRump] = $this->invokePrepareRumps($colony, [$rump]);

        $this->assertSame([], $rumps);
        $this->assertSame([], $buildplansByRump);
        $this->assertSame([], $modulesByRump);
    }

    public function testPrepareRumpsUsesStationBuildplanModulesAndSpecialModules(): void
    {
        $colony = $this->mock(Colony::class);
        $rump = $this->mock(SpacecraftRump::class);
        $buildplan = $this->mock(SpacecraftBuildplan::class);
        $buildplanModule = $this->mock(BuildplanModule::class);
        $module = $this->mock(Module::class);
        $specialModule = $this->mock(Module::class);

        $rump->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(5);
        $rump->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturnTrue();

        $this->buildplanHangarRepository->shouldReceive('getByRump')
            ->with(5)
            ->once()
            ->andReturnNull();

        $this->spacecraftBuildplanRepository->shouldReceive('getStationBuildplanByRump')
            ->with(5)
            ->once()
            ->andReturn($buildplan);

        $buildplan->shouldReceive('getModules')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$buildplanModule]));
        $buildplan->shouldReceive('getRumpId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $buildplanModule->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($module);
        $buildplanModule->shouldReceive('getModuleCount')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $module->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1001);

        $specialModule->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1002);

        $this->moduleRepository->shouldReceive('getBySpecialTypeAndRump')
            ->with($colony, SpacecraftModuleTypeEnum::SPECIAL, 5)
            ->once()
            ->andReturn([$specialModule]);

        [$rumps, $buildplansByRump, $modulesByRump] = $this->invokePrepareRumps($colony, [$rump]);

        $this->assertSame([$rump], $rumps);
        $this->assertSame([5 => $buildplan], $buildplansByRump);
        $this->assertSame([5 => [
            $this->moduleRequirement($module, 3),
            $this->moduleRequirement($specialModule)
        ]], $modulesByRump);
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
     * @return array<int, SpacecraftRump>
     */
    private function invokeGetModuleFabRumps(int $userId): array
    {
        $callable = \Closure::bind(
            function (int $userId): array {
                return $this->getModuleFabRumps($userId);
            },
            $this->subject,
            ShowModuleFab::class
        );

        return $callable($userId);
    }

    /**
     * @param array<SpacecraftRump> $availableRumps
     *
     * @return array<int, SpacecraftBuildplan>
     */
    private function invokeGetAdditionalBuildplans(int $userId, array $availableRumps): array
    {
        $callable = \Closure::bind(
            function (int $userId, array $availableRumps): array {
                return $this->getAdditionalBuildplans($userId, $availableRumps);
            },
            $this->subject,
            ShowModuleFab::class
        );

        return $callable($userId, $availableRumps);
    }

    /**
     * @param array<SpacecraftRump> $rumps
     * @param array<int, ModuleFabricationListItem> $allModules
     * @param array<int, array<int, array{module: Module, requiredAmount: int}>> $exclusiveModulesByRump
     */
    private function invokeSetRumpModules(
        Colony $colony,
        array $rumps,
        array &$allModules,
        array $exclusiveModulesByRump = []
    ): void {
        $callable = \Closure::bind(
            function (Colony $colony, array $rumps, array &$allModules, array $exclusiveModulesByRump): void {
                $this->setRumpModules($colony, $rumps, $allModules, $exclusiveModulesByRump);
            },
            $this->subject,
            ShowModuleFab::class
        );

        $callable($colony, $rumps, $allModules, $exclusiveModulesByRump);
    }

    /**
     * @param array<SpacecraftRump> $rumps
     * @param array<int, ModuleFabricationListItem> $allModules
     * @param array<int, SpacecraftBuildplan> $exclusiveBuildplansByRump
     * @param array<int, array<int, array{module: Module, requiredAmount: int}>> $exclusiveModulesByRump
     * @param array<int, SpacecraftBuildplan> $additionalBuildplans
     */
    private function invokeSetBuildplans(
        array $rumps,
        array &$allModules,
        GameControllerInterface $game,
        array $exclusiveBuildplansByRump,
        array $exclusiveModulesByRump,
        array $additionalBuildplans = []
    ): void {
        $callable = \Closure::bind(
            function (
                array $rumps,
                array &$allModules,
                GameControllerInterface $game,
                array $exclusiveBuildplansByRump,
                array $exclusiveModulesByRump,
                array $additionalBuildplans
            ): void {
                $this->setBuildplans(
                    $rumps,
                    $allModules,
                    $game,
                    $exclusiveBuildplansByRump,
                    $exclusiveModulesByRump,
                    $additionalBuildplans
                );
            },
            $this->subject,
            ShowModuleFab::class
        );

        $callable($rumps, $allModules, $game, $exclusiveBuildplansByRump, $exclusiveModulesByRump, $additionalBuildplans);
    }

    /**
     * @param array<SpacecraftRump> $rumps
     *
     * @return array<int, mixed>
     */
    private function invokePrepareRumps(Colony $colony, array $rumps): array
    {
        $callable = \Closure::bind(
            function (Colony $colony, array $rumps): array {
                return $this->prepareRumps($colony, $rumps);
            },
            $this->subject,
            ShowModuleFab::class
        );

        return $callable($colony, $rumps);
    }

    /** @return array<int, int> */
    private function invokeGetAccumulatedStorageByCommodityId(GameControllerInterface $game): array
    {
        $callable = \Closure::bind(
            function (GameControllerInterface $game): array {
                return $this->getAccumulatedStorageByCommodityId($game);
            },
            $this->subject,
            ShowModuleFab::class
        );

        return $callable($game);
    }

    /**
     * @return array{module: Module, requiredAmount: int}
     */
    private function moduleRequirement(Module $module, int $requiredAmount = 1): array
    {
        return [
            'module' => $module,
            'requiredAmount' => $requiredAmount
        ];
    }
}
