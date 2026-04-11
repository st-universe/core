<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use InvalidArgumentException;
use Mockery\MockInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
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

    private ShowModuleFab $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->colonyLoader = $this->mock(ColonyLoaderInterface::class);
        $this->showModuleFabRequest = $this->mock(ShowModuleFabRequestInterface::class);
        $this->moduleBuildingFunctionRepository = $this->mock(ModuleBuildingFunctionRepositoryInterface::class);
        $this->buildingFunctionRepository = $this->mock(BuildingFunctionRepositoryInterface::class);
        $this->moduleQueueRepository = $this->mock(ModuleQueueRepositoryInterface::class);
        $this->spacecraftRumpRepository = $this->mock(SpacecraftRumpRepositoryInterface::class);
        $this->shipRumpModuleLevelRepository = $this->mock(ShipRumpModuleLevelRepositoryInterface::class);
        $this->spacecraftBuildplanRepository = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $this->moduleRepository = $this->mock(ModuleRepositoryInterface::class);

        $this->subject = new ShowModuleFab(
            $this->colonyLoader,
            $this->showModuleFabRequest,
            $this->moduleBuildingFunctionRepository,
            $this->buildingFunctionRepository,
            $this->moduleQueueRepository,
            $this->spacecraftRumpRepository,
            $this->shipRumpModuleLevelRepository,
            $this->spacecraftBuildplanRepository,
            $this->moduleRepository
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
     */
    private function invokeSetRumpModules(Colony $colony, array $rumps, array &$allModules): void
    {
        $callable = \Closure::bind(
            function (Colony $colony, array $rumps, array &$allModules): void {
                $this->setRumpModules($colony, $rumps, $allModules);
            },
            $this->subject,
            ShowModuleFab::class
        );

        $callable($colony, $rumps, $allModules);
    }
}
