<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Override;
use Stu\Lib\Information\InformationWrapper;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\ShipShutdownInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\StuTestCase;

class ManageCrewTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftCrewCalculatorInterface */
    private MockInterface $shipCrewCalculator;

    /** @var MockInterface&SpacecraftSystemManagerInterface */
    private MockInterface $spacecraftSystemManager;

    /** @var MockInterface&TroopTransferUtilityInterface */
    private MockInterface $troopTransferUtility;

    /** @var MockInterface&ShipShutdownInterface */
    private MockInterface $shipShutdown;

    /** @var MockInterface&SpacecraftLeaverInterface */
    private MockInterface $spacecraftLeaver;

    /** @var MockInterface&ActivatorDeactivatorHelperInterface */
    private MockInterface $helper;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&SpacecraftBuildplanInterface */
    private MockInterface $buildplan;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ManagerProviderInterface */
    private MockInterface $managerProvider;

    private int $shipId = 555;

    /** @var MockInterface&UserInterface */
    private MockInterface $user;

    private ManageCrew $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->shipCrewCalculator = $this->mock(SpacecraftCrewCalculatorInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);
        $this->shipShutdown = $this->mock(ShipShutdownInterface::class);
        $this->spacecraftLeaver = $this->mock(SpacecraftLeaverInterface::class);
        $this->helper = $this->mock(ActivatorDeactivatorHelperInterface::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(ShipInterface::class);
        $this->buildplan = $this->mock(SpacecraftBuildplanInterface::class);
        $this->user = $this->mock(UserInterface::class);
        $this->managerProvider = $this->mock(ManagerProviderInterface::class);

        $this->subject = new ManageCrew(
            $this->shipCrewCalculator,
            $this->spacecraftSystemManager,
            $this->troopTransferUtility,
            $this->shipShutdown,
            $this->spacecraftLeaver,
            $this->helper
        );
    }

    #[Override]
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
        $this->mock(SpacecraftBuildplanInterface::class);

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
        $buildplan = $this->mock(SpacecraftBuildplanInterface::class);
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
        $this->ship->shouldReceive('canMan')
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
        $this->ship->shouldReceive('canMan')
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
        $shipOwner = $this->mock(UserInterface::class);
        $buildplan = $this->mock(SpacecraftBuildplanInterface::class);
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
        $this->ship->shouldReceive('canMan')
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
        $this->ship->shouldReceive('canMan')
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


        $rumpMock = $this->mock(SpacecraftRumpInterface::class);

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
        $this->ship->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->times(3)
            ->andReturn($this->buildplan);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('name');
        $this->ship->shouldReceive('hasShipSystem')
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

        $shipSystemMock = $this->mock(SpacecraftSystemInterface::class);
        $shipSystemMock->shouldReceive('getMode')
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);

        $this->ship->shouldReceive('getShipSystem')
            ->with(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            ->once()
            ->andReturn($shipSystemMock);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(SpacecraftSystemTypeEnum::LIFE_SUPPORT)
            ->once()
            ->andReturn(true);

        $this->buildplan->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn(25);

        $this->helper->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::TROOP_QUARTERS, Mockery::type(InformationWrapper::class))
            ->once()
            ->andReturn(true);

        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::LIFE_SUPPORT, true);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: 10 Crewman wurde(n) hochgebeamt'], $msg);
    }
    public function testManageExpectMannedShip(): void
    {
        $values = ['crew' => ['555' => '42']];


        $rumpMock = $this->mock(SpacecraftRumpInterface::class);

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
        $this->ship->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->times(3)
            ->andReturn($this->buildplan);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('name');
        $this->ship->shouldReceive('hasShipSystem')
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

        $shipSystemMock = $this->mock(SpacecraftSystemInterface::class);
        $shipSystemMock->shouldReceive('getMode')
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);

        $this->ship->shouldReceive('getShipSystem')
            ->with(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            ->once()
            ->andReturn($shipSystemMock);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(SpacecraftSystemTypeEnum::LIFE_SUPPORT)
            ->once()
            ->andReturn(true);

        $this->buildplan->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn(20);

        $this->helper->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::TROOP_QUARTERS, Mockery::type(InformationWrapper::class))
            ->once()
            ->andReturn(true);

        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::LIFE_SUPPORT, true);

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
            ->andReturn($this->mock(SpacecraftBuildplanInterface::class));
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
        $this->ship->shouldReceive('canMan')
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

        $this->ship->shouldReceive('canMan')
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
            ->andReturn($this->mock(SpacecraftBuildplanInterface::class));

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Kein Platz für die Crew auf der providerName'], $msg);
    }

    public function testManageExpectUnmanCappedByProviderSpace(): void
    {
        $shipCrew1 = $this->mock(CrewAssignmentInterface::class);
        $shipCrew2 = $this->mock(CrewAssignmentInterface::class);

        $shipCrewlist = new ArrayCollection([$shipCrew1, $shipCrew2]);

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
            ->once()
            ->andReturn($this->mock(SpacecraftBuildplanInterface::class));
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
        $this->ship->shouldReceive('canMan')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->andReturn($shipCrewlist);

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
        $ownCrew = $this->mock(CrewAssignmentInterface::class);
        $foreignCrew = $this->mock(CrewInterface::class);
        $foreignCrewAssignment = $this->mock(CrewAssignmentInterface::class);

        $shipCrewlist = new ArrayCollection([$ownCrew, $foreignCrewAssignment]);

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
            ->with([$ownCrew])
            ->once();

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(SpacecraftBuildplanInterface::class));
        $this->ship->shouldReceive('canMan')
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
            ->andReturn($this->mock(UserInterface::class));
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

        $this->shipShutdown->shouldReceive('shutdown')
            ->with($this->wrapper)
            ->once();

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: 1 Crewman wurde(n) runtergebeamt'], $msg);
    }
}
