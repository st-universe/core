<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Manager;

use Stu\Lib\Information\InformationWrapper;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Ship\Lib\Auxiliary\ShipShutdownInterface;
use Stu\Module\Ship\Lib\Crew\ShipLeaverInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\StuTestCase;

class ManageCrewTest extends StuTestCase
{
    /** @var MockInterface|ShipCrewCalculatorInterface */
    private MockInterface $shipCrewCalculator;

    /** @var MockInterface|ShipSystemManagerInterface */
    private MockInterface $shipSystemManager;

    /** @var MockInterface|TroopTransferUtilityInterface */
    private MockInterface $troopTransferUtility;

    /** @var MockInterface|ShipShutdownInterface */
    private MockInterface $shipShutdown;

    /** @var MockInterface|ShipLeaverInterface */
    private MockInterface $shipLeaver;

    /** @var MockInterface|ActivatorDeactivatorHelperInterface */
    private MockInterface $helper;

    /** @var MockInterface|ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface|ShipBuildplanInterface */
    private MockInterface $buildplan;

    /** @var MockInterface|ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface|ManagerProviderInterface */
    private MockInterface $managerProvider;

    private int $shipId = 555;

    /** @var MockInterface|UserInterface */
    private MockInterface $user;

    private ManageCrew $subject;

    protected function setUp(): void
    {
        $this->shipCrewCalculator = $this->mock(ShipCrewCalculatorInterface::class);
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);
        $this->shipShutdown = $this->mock(ShipShutdownInterface::class);
        $this->shipLeaver = $this->mock(ShipLeaverInterface::class);
        $this->helper = $this->mock(ActivatorDeactivatorHelperInterface::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(ShipInterface::class);
        $this->buildplan = $this->mock(ShipBuildplanInterface::class);
        $this->user = $this->mock(UserInterface::class);
        $this->managerProvider = $this->mock(ManagerProviderInterface::class);

        $this->subject = new ManageCrew(
            $this->shipCrewCalculator,
            $this->shipSystemManager,
            $this->troopTransferUtility,
            $this->shipShutdown,
            $this->shipLeaver,
            $this->helper
        );
    }

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
        $this->mock(ShipBuildplanInterface::class);

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
        $buildplan = $this->mock(ShipBuildplanInterface::class);
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

    public function testManageExpectNothingWhenShipBuildplanIsNull(): void
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
        $buildplan = $this->mock(ShipBuildplanInterface::class);
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


        $rumpMock = $this->mock(ShipRumpInterface::class);

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
        $this->managerProvider->shouldReceive('addShipCrew')
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
            ->with(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
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

        $shipSystemMock = $this->mock(ShipSystemInterface::class);
        $shipSystemMock->shouldReceive('getMode')
            ->once()
            ->andReturn(ShipSystemModeEnum::MODE_OFF);

        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            ->once()
            ->andReturn($shipSystemMock);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)
            ->once()
            ->andReturn(true);

        $this->buildplan->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn(25);

        $this->helper->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, Mockery::type(InformationWrapper::class))
            ->once()
            ->andReturn(true);

        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: 10 Crewman wurde(n) hochgebeamt'], $msg);
    }
    public function testManageExpectMannedShip(): void
    {
        $values = ['crew' => ['555' => '42']];


        $rumpMock = $this->mock(ShipRumpInterface::class);

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
        $this->managerProvider->shouldReceive('addShipCrew')
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
            ->with(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
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

        $shipSystemMock = $this->mock(ShipSystemInterface::class);
        $shipSystemMock->shouldReceive('getMode')
            ->once()
            ->andReturn(ShipSystemModeEnum::MODE_OFF);

        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            ->once()
            ->andReturn($shipSystemMock);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)
            ->once()
            ->andReturn(true);

        $this->buildplan->shouldReceive('getCrew')
            ->withNoArgs()
            ->andReturn(20);

        $this->helper->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, Mockery::type(InformationWrapper::class))
            ->once()
            ->andReturn(true);

        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);

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
            ->andReturn($this->mock(ShipBuildplanInterface::class));
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
            ->andReturn($this->mock(ShipBuildplanInterface::class));

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Kein Platz für die Crew auf der providerName'], $msg);
    }

    public function testManageExpectUnmanCappedByProviderSpace(): void
    {
        $shipCrew1 = $this->mock(ShipCrewInterface::class);
        $shipCrew2 = $this->mock(ShipCrewInterface::class);

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
            ->andReturn($this->mock(ShipBuildplanInterface::class));
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
        $ownCrew = $this->mock(ShipCrewInterface::class);
        $foreignCrew = $this->mock(CrewInterface::class);
        $foreignShipCrew = $this->mock(ShipCrewInterface::class);

        $shipCrewlist = new ArrayCollection([$ownCrew, $foreignShipCrew]);

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
            ->andReturn($this->mock(ShipBuildplanInterface::class));
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
        $foreignShipCrew->shouldReceive('getCrew')
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

        $this->shipLeaver->shouldReceive('dumpCrewman')
            ->with(
                $foreignShipCrew,
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
