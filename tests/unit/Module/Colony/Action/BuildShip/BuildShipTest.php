<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildShip;

use Mockery;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Spacecraft\Buildplan\BuildplanSignatureCreationInterface;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowModuleScreenBuildplan\ShowModuleScreenBuildplan;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRumpBaseValues;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\StuTestCase;

class BuildShipTest extends StuTestCase
{
    public function testHandleUsesNpcGiftCrewForSignature(): void
    {
        $userId = 123;
        $colonyId = 456;
        $rumpId = 789;
        $planId = 101;
        $moduleId = 202;
        $buildingFunction = BuildingFunctionEnum::FRIGATE_SHIPYARD;

        request::setMockVars([
            'id' => $colonyId,
            'rumpid' => $rumpId,
            'planid' => $planId,
            sprintf('mod_%d', SpacecraftModuleTypeEnum::HULL->value) => [$moduleId]
        ]);

        $colonyFunctionManager = $this->mock(ColonyFunctionManagerInterface::class);
        $shipRumpModuleLevelRepository = $this->mock(ShipRumpModuleLevelRepositoryInterface::class);
        $colonyLoader = $this->mock(ColonyLoaderInterface::class);
        $buildplanModuleRepository = $this->mock(BuildplanModuleRepositoryInterface::class);
        $shipRumpBuildingFunctionRepository = $this->mock(ShipRumpBuildingFunctionRepositoryInterface::class);
        $spacecraftBuildplanRepository = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $moduleRepository = $this->mock(ModuleRepositoryInterface::class);
        $colonyShipQueueRepository = $this->mock(ColonyShipQueueRepositoryInterface::class);
        $spacecraftRumpRepository = $this->mock(SpacecraftRumpRepositoryInterface::class);
        $storageManager = $this->mock(StorageManagerInterface::class);
        $colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $shipCrewCalculator = $this->mock(SpacecraftCrewCalculatorInterface::class);
        $buildplanSignatureCreation = $this->mock(BuildplanSignatureCreationInterface::class);
        $colonyRepository = $this->mock(ColonyRepositoryInterface::class);

        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);
        $colony = $this->mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);
        $rump = $this->mock(SpacecraftRump::class);
        $baseValues = $this->mock(SpacecraftRumpBaseValues::class);
        $buildingFunctionMapping = $this->mock(ShipRumpBuildingFunction::class);
        $moduleLevels = $this->mock(ShipRumpModuleLevel::class);
        $module = $this->mock(Module::class);
        $plan = $this->mock(SpacecraftBuildplan::class);
        $informationWrapper = new InformationWrapper();

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $game->shouldReceive('setView')
            ->with(ShowModuleScreenBuildplan::VIEW_IDENTIFIER)
            ->once();
        $game->shouldReceive('setViewContext')
            ->with(ViewContextTypeEnum::BUILDPLAN, $planId)
            ->once();
        $game->shouldReceive('getInfo')
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
        $colony->shouldReceive('getChangeable')
            ->withNoArgs()
            ->andReturn($changeable);
        $colony->shouldReceive('isBlocked')
            ->withNoArgs()
            ->andReturn(false);

        $changeable->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(100);

        $spacecraftRumpRepository->shouldReceive('find')
            ->with($rumpId)
            ->once()
            ->andReturn($rump);

        $rump->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($rumpId);
        $rump->shouldReceive('getEpsCost')
            ->withNoArgs()
            ->andReturn(10);
        $rump->shouldReceive('getBaseValues')
            ->withNoArgs()
            ->andReturn($baseValues);

        $baseValues->shouldReceive('getSpecialSlots')
            ->withNoArgs()
            ->andReturn(0);

        $shipRumpBuildingFunctionRepository->shouldReceive('getByShipRump')
            ->with($rump)
            ->once()
            ->andReturn([$buildingFunctionMapping]);
        $buildingFunctionMapping->shouldReceive('getBuildingFunction')
            ->withNoArgs()
            ->andReturn($buildingFunction);
        $colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, $buildingFunction)
            ->once()
            ->andReturn(true);

        $colonyShipQueueRepository->shouldReceive('getAmountByColonyAndBuildingFunctionAndMode')
            ->with($colonyId, $buildingFunction, Mockery::any())
            ->twice()
            ->andReturn(0);

        $shipRumpModuleLevelRepository->shouldReceive('getByShipRump')
            ->with($rump)
            ->once()
            ->andReturn($moduleLevels);
        $moduleLevels->shouldReceive('isMandatory')
            ->withAnyArgs()
            ->andReturn(false);

        $moduleRepository->shouldReceive('find')
            ->with($moduleId)
            ->once()
            ->andReturn($module);

        $shipCrewCalculator->shouldNotReceive('getCrewUsage');
        $shipCrewCalculator->shouldReceive('getMaxCrewCountByRump')
            ->with($rump)
            ->once()
            ->andReturn(10);

        $spacecraftBuildplanRepository->shouldReceive('find')
            ->with($planId)
            ->once()
            ->andReturn($plan);
        $spacecraftBuildplanRepository->shouldNotReceive('getByUserShipRumpAndSignature');

        $plan->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn($userId);
        $plan->shouldReceive('getRumpId')
            ->withNoArgs()
            ->andReturn($rumpId);
        $plan->shouldReceive('getNpcGift')
            ->withNoArgs()
            ->andReturn(true);
        $plan->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(7);
        $plan->shouldReceive('getSignature')
            ->withNoArgs()
            ->once()
            ->andReturn('npc-signature');
        $plan->shouldReceive('getCount')
            ->withNoArgs()
            ->andReturn(0);

        $buildplanSignatureCreation->shouldReceive('createSignature')
            ->with(
                Mockery::on(fn (array $modules): bool => $modules[$moduleId] === $module),
                7
            )
            ->once()
            ->andReturn('npc-signature');

        $subject = new BuildShip(
            $colonyFunctionManager,
            $shipRumpModuleLevelRepository,
            $colonyLoader,
            $buildplanModuleRepository,
            $shipRumpBuildingFunctionRepository,
            $spacecraftBuildplanRepository,
            $moduleRepository,
            $colonyShipQueueRepository,
            $spacecraftRumpRepository,
            $storageManager,
            $colonyLibFactory,
            $shipCrewCalculator,
            $buildplanSignatureCreation,
            $colonyRepository
        );

        $subject->handle($game);

        $this->assertSame(['Dieser Bauplan ist nicht mehr baubar'], $informationWrapper->getInformations());
    }
}
