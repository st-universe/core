<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Orm\Entity\SpacecraftInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SystemDataDeserializerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Reactor\ReactorWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\Ui\StateIconAndTitle;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftWrapperFactoryTest extends StuTestCase
{
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;
    private MockInterface&ReactorWrapperFactoryInterface $reactorWrapperFactory;
    private MockInterface&ColonyLibFactoryInterface $colonyLibFactory;
    private MockInterface&TorpedoTypeRepositoryInterface $torpedoTypeRepository;
    private MockInterface&GameControllerInterface $game;
    private MockInterface&SpacecraftStateChangerInterface $spacecraftStateChanger;
    private MockInterface&RepairUtilInterface $repairUtil;
    private MockInterface&StateIconAndTitle $stateIconAndTitle;
    private MockInterface&SystemDataDeserializerInterface $systemDataDeserializer;

    private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->reactorWrapperFactory = $this->mock(ReactorWrapperFactoryInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->torpedoTypeRepository = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->spacecraftStateChanger = $this->mock(SpacecraftStateChangerInterface::class);
        $this->repairUtil = $this->mock(RepairUtilInterface::class);
        $this->stateIconAndTitle = $this->mock(StateIconAndTitle::class);
        $this->systemDataDeserializer = $this->mock(SystemDataDeserializerInterface::class);

        $this->spacecraftWrapperFactory = new SpacecraftWrapperFactory(
            $this->spacecraftSystemManager,
            $this->reactorWrapperFactory,
            $this->colonyLibFactory,
            $this->torpedoTypeRepository,
            $this->game,
            $this->spacecraftStateChanger,
            $this->repairUtil,
            $this->stateIconAndTitle,
            $this->systemDataDeserializer
        );
    }

    public function testWrapShips(): void
    {
        $shipA = $this->mock(ShipInterface::class);
        $shipB = $this->mock(ShipInterface::class);
        $shipArray = [12 => $shipA, 27 => $shipB];

        $result = $this->spacecraftWrapperFactory->wrapShips($shipArray);

        $this->assertEquals(2, count($result));
        $this->assertEquals($shipA, $result[12]->get());
        $this->assertEquals($shipB, $result[27]->get());
    }

    public function testWrapSpacecraftsAsGroups(): void
    {
        $user = $this->mock(UserInterface::class);
        $shipSolo1 = $this->mock(ShipInterface::class);
        $stationSolo2 = $this->mock(StationInterface::class);
        $shipFleetLowSort2 = $this->mock(ShipInterface::class);
        $shipFleetLowSort1 = $this->mock(ShipInterface::class);
        $shipFleetHighSort = $this->mock(ShipInterface::class);
        $fleetLowSort = $this->mock(FleetInterface::class);
        $fleetHighSort = $this->mock(FleetInterface::class);

        $spacecrafts = new ArrayCollection([
            12 => $shipSolo1,
            27 => $stationSolo2,
            7 => $shipFleetLowSort2,
            6 => $shipFleetLowSort1,
            5 => $shipFleetHighSort
        ]);

        $shipSolo1->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn(null);
        $shipSolo1->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);
        $shipFleetLowSort2->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn($fleetLowSort);
        $shipFleetLowSort2->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(true);
        $shipFleetLowSort1->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn($fleetLowSort);
        $shipFleetLowSort1->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);
        $shipFleetHighSort->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn($fleetHighSort);
        $shipFleetHighSort->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(true);

        $shipSolo1->shouldReceive('getRump->getCategoryId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(SpacecraftRumpCategoryEnum::SHIP_CATEGORY_STATION);
        $stationSolo2->shouldReceive('getRump->getCategoryId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(SpacecraftRumpCategoryEnum::SHIP_CATEGORY_STATION);
        $shipFleetLowSort2->shouldReceive('getRump->getCategoryId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(SpacecraftRumpCategoryEnum::SHIP_CATEGORY_STATION);
        $shipFleetLowSort1->shouldReceive('getRump->getCategoryId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(SpacecraftRumpCategoryEnum::SHIP_CATEGORY_STATION);
        $shipFleetHighSort->shouldReceive('getRump->getCategoryId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(SpacecraftRumpCategoryEnum::SHIP_CATEGORY_STATION);

        $shipSolo1->shouldReceive('getRump->getRoleId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(SpacecraftRumpRoleEnum::SHIP_ROLE_BASE);
        $stationSolo2->shouldReceive('getRump->getRoleId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(SpacecraftRumpRoleEnum::SHIP_ROLE_BASE);
        $shipFleetLowSort2->shouldReceive('getRump->getRoleId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(SpacecraftRumpRoleEnum::SHIP_ROLE_BASE);
        $shipFleetLowSort1->shouldReceive('getRump->getRoleId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(SpacecraftRumpRoleEnum::SHIP_ROLE_BASE);
        $shipFleetHighSort->shouldReceive('getRump->getRoleId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(SpacecraftRumpRoleEnum::SHIP_ROLE_BASE);

        $shipSolo1->shouldReceive('getRumpId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(999);
        $stationSolo2->shouldReceive('getRumpId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(999);
        $shipFleetLowSort2->shouldReceive('getRumpId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(999);
        $shipFleetLowSort1->shouldReceive('getRumpId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(999);
        $shipFleetHighSort->shouldReceive('getRumpId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(999);

        $shipSolo1->shouldReceive('getName')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(333);
        $stationSolo2->shouldReceive('getName')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(333);
        $shipFleetLowSort2->shouldReceive('getName')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(333);
        $shipFleetLowSort1->shouldReceive('getName')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(333);
        $shipFleetHighSort->shouldReceive('getName')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(333);

        $fleetLowSort->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(34);
        $fleetHighSort->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(17);

        $fleetLowSort->shouldReceive('getSort')
            ->withNoArgs()
            ->andReturn(100);
        $fleetHighSort->shouldReceive('getSort')
            ->withNoArgs()
            ->andReturn(200);

        $fleetLowSort->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $fleetHighSort->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $fleetLowSort->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('FLEET_LOW_SORT');
        $fleetHighSort->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('FLEET_HIGH_SORT');

        $groups = $this->spacecraftWrapperFactory->wrapSpacecraftsAsGroups($spacecrafts);

        $this->assertEquals(3, $groups->count());
        $this->assertEquals([
            '200_17',
            '100_34',
            '9223372036854775807_0',
        ], $groups->getKeys());

        $group1 = $groups->get('9223372036854775807_0');
        $group2 = $groups->get('100_34');
        $group3 = $groups->get('200_17');

        $this->assertEquals('Einzelschiffe', $group1->getName());
        $this->assertEquals('FLEET_LOW_SORT', $group2->getName());
        $this->assertEquals('FLEET_HIGH_SORT', $group3->getName());
        $this->assertNull($group1->getUser());
        $this->assertEquals($user, $group2->getUser());
        $this->assertEquals($user, $group3->getUser());

        $this->assertEquals([
            0 => $shipSolo1,
            1 => $stationSolo2
        ], $group1->getWrappers()->map(fn(SpacecraftWrapperInterface $wrapper): SpacecraftInterface => $wrapper->get())->toArray());
        $this->assertEquals([
            0 => $shipFleetLowSort2,
            1 => $shipFleetLowSort1
        ], $group2->getWrappers()->map(fn(SpacecraftWrapperInterface $wrapper): SpacecraftInterface => $wrapper->get())->toArray());
        $this->assertEquals([
            0 => $shipFleetHighSort
        ], $group3->getWrappers()->map(fn(SpacecraftWrapperInterface $wrapper): SpacecraftInterface => $wrapper->get())->toArray());
    }
}
