<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Creation;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\Control\AlertStateManagerInterface;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\Data\WarpDriveSystemData;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftStartupInterface;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftConfiguratorTest extends StuTestCase
{
    private MockInterface&SpacecraftWrapperInterface $wrapper;
    private MockInterface&TorpedoTypeRepositoryInterface $torpedoTypeRepository;
    private MockInterface&ShipTorpedoManagerInterface $torpedoManager;
    private MockInterface&CrewCreatorInterface $crewCreator;
    private MockInterface&CrewAssignmentRepositoryInterface $shipCrewRepository;
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;
    private MockInterface&AlertStateManagerInterface $alertStateManager;
    private MockInterface&SpacecraftStartupInterface $spacecraftStartup;

    private MockInterface&Spacecraft $spacecraft;

    private SpacecraftConfiguratorInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $this->torpedoTypeRepository = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->torpedoManager = $this->mock(ShipTorpedoManagerInterface::class);
        $this->crewCreator = $this->mock(CrewCreatorInterface::class);
        $this->shipCrewRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->alertStateManager = $this->mock(AlertStateManagerInterface::class);
        $this->spacecraftStartup = $this->mock(SpacecraftStartupInterface::class);

        $this->spacecraft = $this->mock(Spacecraft::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->spacecraft);

        $this->subject = new SpacecraftConfigurator(
            $this->wrapper,
            $this->torpedoTypeRepository,
            $this->torpedoManager,
            $this->crewCreator,
            $this->shipCrewRepository,
            $this->spacecraftRepository,
            $this->alertStateManager,
            $this->spacecraftStartup
        );
    }

    public function testSetLocation(): void
    {
        $location = $this->mock(Location::class);

        $this->spacecraft->shouldReceive('setLocation')
            ->with($location)
            ->once();

        $this->subject->setLocation($location);
    }

    public function testLoadEps(): void
    {
        $epsSystem = $this->mock(EpsSystemData::class);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);

        $epsSystem->shouldReceive('getTheoreticalMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn(101);
        $epsSystem->shouldReceive('setEps')
            ->with(70)
            ->once()
            ->andReturnSelf();
        $epsSystem->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->subject->loadEps(70);
    }

    public function testLoadBattery(): void
    {
        $epsSystem = $this->mock(EpsSystemData::class);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);

        $epsSystem->shouldReceive('getMaxBattery')
            ->withNoArgs()
            ->once()
            ->andReturn(101);
        $epsSystem->shouldReceive('setBattery')
            ->with(70)
            ->once()
            ->andReturnSelf();
        $epsSystem->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->subject->loadBattery(70);
    }

    public function testLoadReactor(): void
    {
        $reactorWrapper = $this->mock(ReactorWrapperInterface::class);

        $this->wrapper->shouldReceive('getReactorWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($reactorWrapper);

        $reactorWrapper->shouldReceive('getCapacity')
            ->withNoArgs()
            ->once()
            ->andReturn(101);
        $reactorWrapper->shouldReceive('setLoad')
            ->with(70)
            ->once()
            ->andReturnSelf();

        $this->subject->loadReactor(70);
    }

    public function testLoadWarpdrive(): void
    {
        $warpdriveSystemData = $this->mock(WarpDriveSystemData::class);

        $this->wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($warpdriveSystemData);

        $warpdriveSystemData->shouldReceive('getMaxWarpdrive')
            ->withNoArgs()
            ->once()
            ->andReturn(101);
        $warpdriveSystemData->shouldReceive('setWarpDrive')
            ->with(70)
            ->once()
            ->andReturnSelf();
        $warpdriveSystemData->shouldReceive('update')
            ->withNoArgs();

        $this->subject->loadWarpdrive(70);
    }

    public function testMaxOutSystems(): void
    {
        $epsSystem = $this->mock(EpsSystemData::class);
        $reactorWrapper = $this->mock(ReactorWrapperInterface::class);
        $warpdriveSystemData = $this->mock(WarpDriveSystemData::class);

        $this->spacecraft->shouldReceive('getMaxShield')
            ->withNoArgs()
            ->once()
            ->andReturn(4242);
        $this->spacecraft->shouldReceive('getCondition->setShield')
            ->with(4242)
            ->once();

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->andReturn($epsSystem);
        $epsSystem->shouldReceive('getMaxBattery')
            ->withNoArgs()
            ->once()
            ->andReturn(300);
        $epsSystem->shouldReceive('setBattery')
            ->with(300)
            ->once()
            ->andReturnSelf();
        $epsSystem->shouldReceive('update')
            ->withNoArgs()
            ->once();
        $epsSystem->shouldReceive('getTheoreticalMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn(23);
        $epsSystem->shouldReceive('setEps')
            ->with(23)
            ->once()
            ->andReturnSelf();
        $epsSystem->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->wrapper->shouldReceive('getReactorWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($reactorWrapper);
        $reactorWrapper->shouldReceive('getCapacity')
            ->withNoArgs()
            ->once()
            ->andReturn(200);
        $reactorWrapper->shouldReceive('setLoad')
            ->with(200)
            ->once()
            ->andReturnSelf();

        $this->wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($warpdriveSystemData);
        $warpdriveSystemData->shouldReceive('getMaxWarpdrive')
            ->withNoArgs()
            ->once()
            ->andReturn(101);
        $warpdriveSystemData->shouldReceive('setWarpDrive')
            ->with(101)
            ->once()
            ->andReturnSelf();
        $warpdriveSystemData->shouldReceive('update')
            ->withNoArgs();

        $this->subject->maxOutSystems();
    }

    public function testCreateCrew(): void
    {
        $buildplan = $this->mock(SpacecraftBuildplan::class);
        $crewAssignment1 = $this->mock(CrewAssignment::class);
        $crewAssignment2 = $this->mock(CrewAssignment::class);

        $this->spacecraft->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($buildplan);
        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(42);
        $this->spacecraft->shouldReceive('getCrewAssignments->add')
            ->with($crewAssignment1)
            ->once();
        $this->spacecraft->shouldReceive('getCrewAssignments->add')
            ->with($crewAssignment2)
            ->once();

        $crewAssignment1->shouldReceive('setSpacecraft')
            ->with($this->spacecraft)
            ->once();
        $crewAssignment2->shouldReceive('setSpacecraft')
            ->with($this->spacecraft)
            ->once();

        $this->crewCreator->shouldReceive('create')
            ->with(42)
            ->times(2)
            ->andReturn($crewAssignment1, $crewAssignment2);

        $this->shipCrewRepository->shouldReceive('save')
            ->with($crewAssignment1)
            ->once();
        $this->shipCrewRepository->shouldReceive('save')
            ->with($crewAssignment2)
            ->once();

        $this->spacecraftStartup->shouldReceive('startup')
            ->with($this->wrapper)
            ->once();

        $this->subject->createCrew(2);
    }

    public function testTransferCrew(): void
    {
        $buildplan = $this->mock(SpacecraftBuildplan::class);
        $crewProvider = $this->mock(EntityWithCrewAssignmentsInterface::class);

        $buildplan->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->spacecraft->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($buildplan);

        $this->crewCreator->shouldReceive('createCrewAssignments')
            ->with($this->spacecraft, $crewProvider, 42)
            ->once();

        $this->spacecraftStartup->shouldReceive('startup')
            ->with($this->wrapper)
            ->once();

        $this->subject->transferCrew($crewProvider);
    }

    public function testSetAlertState(): void
    {
        $this->alertStateManager->shouldReceive('setAlertState')
            ->with($this->wrapper, SpacecraftAlertStateEnum::ALERT_RED)
            ->once();

        $this->subject->setAlertState(SpacecraftAlertStateEnum::ALERT_RED);
    }

    public function testSetTorpedoWithTypeId(): void
    {
        $torpedoType = $this->mock(TorpedoType::class);

        $this->spacecraft->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->andReturn(55);

        $this->torpedoTypeRepository->shouldReceive('find')
            ->with(42)
            ->once()
            ->andReturn($torpedoType);

        $this->torpedoManager->shouldReceive('changeTorpedo')
            ->with($this->wrapper, 55, $torpedoType)
            ->once();

        $this->subject->setTorpedo(42);
    }

    public function testSetSpacecraftName(): void
    {
        $this->spacecraft->shouldReceive('setName')
            ->with('NAME')
            ->once();

        $this->subject->setSpacecraftName('NAME');
    }

    public function testFinishConfiguration(): void
    {
        $this->spacecraftRepository->shouldReceive('save')
            ->with($this->spacecraft)
            ->once();

        $result = $this->subject->finishConfiguration();

        $this->assertSame($this->wrapper, $result);
    }
}
