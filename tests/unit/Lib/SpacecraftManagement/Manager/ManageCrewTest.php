<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftStartupInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class ManageCrewTest extends StuTestCase
{
    private MockInterface&SpacecraftCrewCalculatorInterface $shipCrewCalculator;
    private MockInterface&TroopTransferUtilityInterface $troopTransferUtility;
    private MockInterface&SpacecraftShutdownInterface $spacecraftShutdown;
    private MockInterface&SpacecraftLeaverInterface $spacecraftLeaver;
    private MockInterface&ActivatorDeactivatorHelperInterface $helper;
    private MockInterface&SpacecraftStartupInterface $spacecraftStartup;

    private MockInterface&ShipWrapperInterface $wrapper;

    private MockInterface&SpacecraftBuildplan $buildplan;

    private MockInterface&Ship $ship;

    private MockInterface&ManagerProviderInterface $managerProvider;

    private int $shipId = 555;

    private MockInterface&User $user;

    private ManageCrew $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->shipCrewCalculator = $this->mock(SpacecraftCrewCalculatorInterface::class);
        $this->troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);
        $this->spacecraftShutdown = $this->mock(SpacecraftShutdownInterface::class);
        $this->spacecraftLeaver = $this->mock(SpacecraftLeaverInterface::class);
        $this->helper = $this->mock(ActivatorDeactivatorHelperInterface::class);
        $this->spacecraftStartup = $this->mock(SpacecraftStartupInterface::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(Ship::class);
        $this->buildplan = $this->mock(SpacecraftBuildplan::class);
        $this->user = $this->mock(User::class);
        $this->managerProvider = $this->mock(ManagerProviderInterface::class);

        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1111);

        $this->subject = new ManageCrew(
            $this->shipCrewCalculator,
            $this->troopTransferUtility,
            $this->spacecraftShutdown,
            $this->spacecraftLeaver,
            $this->helper,
            $this->spacecraftStartup
        );
    }

    #[\Override]
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testManageExpectErrorWhenValuesNotPresent(): void
    {
        static::expectExceptionMessage('value array not existent');
        static::expectException(RuntimeException::class);

        $values = ['foo' => '42'];

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenNotInValues(): void
    {
        $values = ['crew' => ['5' => '42']];
        $this->mock(SpacecraftBuildplan::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenShipCantBeManned(): void
    {
        $buildplan = $this->mock(SpacecraftBuildplan::class);
        $values = ['crew' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);
        $this->wrapper->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($buildplan);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenSpacecraftBuildplanIsNull(): void
    {
        $values = ['crew' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);
        $this->wrapper->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenForeignShip(): void
    {
        $shipOwner = $this->mock(User::class);
        $buildplan = $this->mock(SpacecraftBuildplan::class);
        $values = ['crew' => ['555' => '42']];

        $shipOwner->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2222);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);
        $this->wrapper->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($shipOwner);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($buildplan);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenNoCrewOnProvider(): void
    {
        $values = ['crew' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->managerProvider->shouldReceive('getFreeCrewAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $this->managerProvider->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('Kolonie');

        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->andReturn(30);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->wrapper->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($this->buildplan);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('name');

        $this->buildplan->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn(42);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Keine Crew auf der Kolonie vorhanden'], $msg);
    }

    public function testManageExpectManningCappedByProvider(): void
    {
        $values = ['crew' => ['555' => '42']];


        $rumpMock = $this->mock(SpacecraftRump::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->managerProvider->shouldReceive('getFreeCrewAmount')
            ->withNoArgs()
            ->andReturn(10);
        $this->managerProvider->shouldReceive('addCrewAssignment')
            ->with($this->ship, 10)
            ->once();

        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->andReturn(30);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->wrapper->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->times(2)
            ->andReturn($this->buildplan);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('name');
        $this->ship->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            ->once()
            ->andReturn(true);

        $this->ship->shouldReceive('getRump')
            ->withNoArgs()
            ->once()
            ->andReturn($rumpMock);

        $this->shipCrewCalculator->shouldReceive('getMaxCrewCountByRump')
            ->with($rumpMock)
            ->once()
            ->andReturn(35);

        $shipSystemMock = $this->mock(SpacecraftSystem::class);
        $shipSystemMock->shouldReceive('getMode')
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            ->once()
            ->andReturn($shipSystemMock);

        $this->buildplan->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn(25);

        $this->helper->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::TROOP_QUARTERS, Mockery::type(InformationWrapper::class))
            ->once()
            ->andReturn(true);

        $this->spacecraftStartup->shouldReceive('startup')
            ->with($this->wrapper);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: 10 Crewman wurde(n) hochgebeamt'], $msg);
    }
    public function testManageExpectMannedShip(): void
    {
        $values = ['crew' => ['555' => '42']];


        $rumpMock = $this->mock(SpacecraftRump::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->managerProvider->shouldReceive('getFreeCrewAmount')
            ->withNoArgs()
            ->andReturn(42);
        $this->managerProvider->shouldReceive('addCrewAssignment')
            ->with($this->ship, 22)
            ->once();

        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->andReturn(20);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->wrapper->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->times(2)
            ->andReturn($this->buildplan);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('name');
        $this->ship->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            ->once()
            ->andReturn(true);

        $this->ship->shouldReceive('getRump')
            ->withNoArgs()
            ->once()
            ->andReturn($rumpMock);

        $this->shipCrewCalculator->shouldReceive('getMaxCrewCountByRump')
            ->with($rumpMock)
            ->once()
            ->andReturn(40);

        $shipSystemMock = $this->mock(SpacecraftSystem::class);
        $shipSystemMock->shouldReceive('getMode')
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            ->once()
            ->andReturn($shipSystemMock);

        $this->buildplan->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn(20);

        $this->helper->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::TROOP_QUARTERS, Mockery::type(InformationWrapper::class))
            ->once()
            ->andReturn(true);

        $this->spacecraftStartup->shouldReceive('startup')
            ->with($this->wrapper);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: 22 Crewman wurde(n) hochgebeamt'], $msg);
    }

    public function testManageExpectNothingWhenCrewCountUnchanged(): void
    {
        $values = ['crew' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(SpacecraftBuildplan::class));
        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->andReturn(42);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->wrapper->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenProviderFull(): void
    {
        $values = ['crew' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->user);
        $this->managerProvider->shouldReceive('getFreeCrewStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $this->managerProvider->shouldReceive('getName')
            ->with()
            ->once()
            ->andReturn('providerName');

        $this->wrapper->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->andReturn(99);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('name');
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(SpacecraftBuildplan::class));

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Kein Platz für die Crew auf der providerName'], $msg);
    }

    public function testManageExpectUnmanCappedByProviderSpace(): void
    {
        $shipCrew1 = $this->mock(CrewAssignment::class);
        $shipCrew2 = $this->mock(CrewAssignment::class);

        $shipCrewlist = new ArrayCollection([$shipCrew1, $shipCrew2]);
        $rumpMock = $this->mock(SpacecraftRump::class);
        $buildplan = $this->mock(SpacecraftBuildplan::class);

        $values = ['crew' => ['555' => '0']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->user);
        $this->managerProvider->shouldReceive('getFreeCrewStorage')
            ->withNoArgs()
            ->andReturn(1);
        $this->managerProvider->shouldReceive('addCrewAssignments')
            ->with([$shipCrew1])
            ->once();

        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->times(3)
            ->andReturn($buildplan);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->andReturn(2);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('name');
        $this->wrapper->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->andReturn($shipCrewlist);
        $this->ship->shouldReceive('getRump')
            ->withNoArgs()
            ->once()
            ->andReturn($rumpMock);
        $this->ship->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            ->once()
            ->andReturn(false);

        $buildplan->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->shipCrewCalculator->shouldReceive('getMaxCrewCountByRump')
            ->with($rumpMock)
            ->once()
            ->andReturn(5);

        $this->troopTransferUtility->shouldReceive('ownCrewOnTarget')
            ->with($this->user, $this->ship)
            ->once()
            ->andReturn(2);

        $shipCrew1->shouldReceive('getCrew->getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $shipCrew2->shouldReceive('getCrew->getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: 1 Crewman wurde(n) runtergebeamt'], $msg);
    }

    public function testManageExpectCompleteUnmanWhenEnoughSpaceOnProvider(): void
    {
        $ownCrew = $this->mock(CrewAssignment::class);
        $foreignCrew = $this->mock(Crew::class);
        $foreignCrewUser = $this->mock(User::class);
        $foreignCrewAssignment = $this->mock(CrewAssignment::class);
        $rumpMock = $this->mock(SpacecraftRump::class);
        $buildplan = $this->mock(SpacecraftBuildplan::class);

        $shipCrewlist = new ArrayCollection([$ownCrew, $foreignCrewAssignment]);

        $values = ['crew' => ['555' => '0']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);

        $foreignCrewUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2222);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->user);
        $this->managerProvider->shouldReceive('getFreeCrewStorage')
            ->withNoArgs()
            ->andReturn(1);
        $this->managerProvider->shouldReceive('addCrewAssignments')
            ->with([$ownCrew])
            ->once();

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->times(3)
            ->andReturn($buildplan);
        $this->wrapper->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('name');
        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->andReturn(2);
        $this->ship->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->andReturn($shipCrewlist);
        $this->ship->shouldReceive('getRump')
            ->withNoArgs()
            ->once()
            ->andReturn($rumpMock);
        $this->ship->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            ->once()
            ->andReturn(false);

        $buildplan->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->shipCrewCalculator->shouldReceive('getMaxCrewCountByRump')
            ->with($rumpMock)
            ->once()
            ->andReturn(5);

        $this->troopTransferUtility->shouldReceive('ownCrewOnTarget')
            ->with($this->user, $this->ship)
            ->once()
            ->andReturn(1);

        $this->user->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('spieler');

        $ownCrew->shouldReceive('getCrew->getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $foreignCrewAssignment->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn($foreignCrew);
        $foreignCrew->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($foreignCrewUser);
        $foreignCrew->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('Foreigner');

        $this->spacecraftLeaver->shouldReceive('dumpCrewman')
            ->with(
                $foreignCrewAssignment,
                'Die Dienste von Crewman Foreigner werden nicht mehr auf der Station name von Spieler spieler benötigt.'
            )
            ->once()
            ->andReturn('Foreigner');

        $this->spacecraftShutdown->shouldReceive('shutdown')
            ->with($this->wrapper)
            ->once();

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: 1 Crewman wurde(n) runtergebeamt'], $msg);
    }
}
