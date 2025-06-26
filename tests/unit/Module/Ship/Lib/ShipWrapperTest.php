<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SystemDataDeserializerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonySurfaceInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Reactor\ReactorWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\Ui\StateIconAndTitle;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

class ShipWrapperTest extends StuTestCase
{
    private MockInterface&Ship $ship;
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;
    private MockInterface&SystemDataDeserializerInterface $systemDataDeserializer;
    private MockInterface&TorpedoTypeRepositoryInterface $torpedoTypeRepository;
    private MockInterface&GameControllerInterface $game;
    private MockInterface&SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;
    private MockInterface&ReactorWrapperFactoryInterface $reactorWrapperFactory;
    private MockInterface&SpacecraftStateChangerInterface $spacecraftStateChanger;
    private MockInterface&RepairUtilInterface $repairUtil;
    private MockInterface&StateIconAndTitle $stateIconAndTitle;
    private MockInterface&ColonyLibFactoryInterface $colonyLibFactory;

    private ShipWrapperInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->ship = $this->mock(Ship::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->systemDataDeserializer = $this->mock(SystemDataDeserializerInterface::class);
        $this->torpedoTypeRepository = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->reactorWrapperFactory = $this->mock(ReactorWrapperFactoryInterface::class);
        $this->spacecraftStateChanger = $this->mock(SpacecraftStateChangerInterface::class);
        $this->repairUtil = $this->mock(RepairUtilInterface::class);
        $this->stateIconAndTitle = $this->mock(StateIconAndTitle::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);

        $this->subject = new ShipWrapper(
            $this->ship,
            $this->spacecraftSystemManager,
            $this->systemDataDeserializer,
            $this->torpedoTypeRepository,
            $this->game,
            $this->spacecraftWrapperFactory,
            $this->reactorWrapperFactory,
            $this->spacecraftStateChanger,
            $this->repairUtil,
            $this->stateIconAndTitle,
            $this->colonyLibFactory
        );
    }

    public function testCanLandOnCurrentColonyExpectFalseWhenNoRumpCommodity(): void
    {
        $this->ship->shouldReceive('getRump->getCommodity')
            ->withNoArgs()
            ->andReturn(null);

        $result = $this->subject->canLandOnCurrentColony();

        $this->assertFalse($result);
    }

    public function testCanLandOnCurrentColonyExpectFalseWhenShuttle(): void
    {
        $this->ship->shouldReceive('getRump->getCommodity')
            ->withNoArgs()
            ->andReturn($this->mock(Commodity::class));
        $this->ship->shouldReceive('isShuttle')
            ->withNoArgs()
            ->andReturn(true);

        $result = $this->subject->canLandOnCurrentColony();

        $this->assertFalse($result);
    }

    public function testCanLandOnCurrentColonyExpectFalseWhenNotOnStarMap(): void
    {
        $this->ship->shouldReceive('getRump->getCommodity')
            ->withNoArgs()
            ->andReturn($this->mock(Commodity::class));
        $this->ship->shouldReceive('isShuttle')
            ->withNoArgs()
            ->andReturn(false);
        $this->ship->shouldReceive('getStarsystemMap')
            ->withNoArgs()
            ->andReturn(null);

        $result = $this->subject->canLandOnCurrentColony();

        $this->assertFalse($result);
    }

    public function testCanLandOnCurrentColonyExpectFalseWhenNotAboveColony(): void
    {
        $starmap = $this->mock(StarSystemMap::class);

        $this->ship->shouldReceive('getRump->getCommodity')
            ->withNoArgs()
            ->andReturn($this->mock(Commodity::class));
        $this->ship->shouldReceive('isShuttle')
            ->withNoArgs()
            ->andReturn(false);
        $this->ship->shouldReceive('getStarsystemMap')
            ->withNoArgs()
            ->andReturn($starmap);

        $starmap->shouldReceive('getColony')
            ->withNoArgs()
            ->andReturn(null);

        $result = $this->subject->canLandOnCurrentColony();

        $this->assertFalse($result);
    }

    public function testCanLandOnCurrentColonyExpectFalseWhenColonyOfOtherUser(): void
    {
        $user = $this->mock(User::class);
        $starmap = $this->mock(StarSystemMap::class);
        $colony = $this->mock(Colony::class);

        $this->ship->shouldReceive('getRump->getCommodity')
            ->withNoArgs()
            ->andReturn($this->mock(Commodity::class));
        $this->ship->shouldReceive('isShuttle')
            ->withNoArgs()
            ->andReturn(false);
        $this->ship->shouldReceive('getStarsystemMap')
            ->withNoArgs()
            ->andReturn($starmap);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $starmap->shouldReceive('getColony')
            ->withNoArgs()
            ->andReturn($colony);

        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($this->mock(User::class));

        $result = $this->subject->canLandOnCurrentColony();

        $this->assertFalse($result);
    }

    public function testCanLandOnCurrentColonyExpectFalseWhenNoAirfieldOnColony(): void
    {
        $user = $this->mock(User::class);
        $starmap = $this->mock(StarSystemMap::class);
        $colony = $this->mock(Colony::class);
        $surface = $this->mock(ColonySurfaceInterface::class);

        $this->ship->shouldReceive('getRump->getCommodity')
            ->withNoArgs()
            ->andReturn($this->mock(Commodity::class));
        $this->ship->shouldReceive('isShuttle')
            ->withNoArgs()
            ->andReturn(false);
        $this->ship->shouldReceive('getStarsystemMap')
            ->withNoArgs()
            ->andReturn($starmap);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $starmap->shouldReceive('getColony')
            ->withNoArgs()
            ->andReturn($colony);

        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $this->colonyLibFactory->shouldReceive('createColonySurface')
            ->with($colony)
            ->andReturn($surface);

        $surface->shouldReceive('hasAirfield')
            ->withNoArgs()
            ->andReturn(false);

        $result = $this->subject->canLandOnCurrentColony();

        $this->assertFalse($result);
    }

    public function testCanLandOnCurrentColonyExpectWhenOwnColonyHasAirfield(): void
    {
        $user = $this->mock(User::class);
        $starmap = $this->mock(StarSystemMap::class);
        $colony = $this->mock(Colony::class);
        $surface = $this->mock(ColonySurfaceInterface::class);

        $this->ship->shouldReceive('getRump->getCommodity')
            ->withNoArgs()
            ->andReturn($this->mock(Commodity::class));
        $this->ship->shouldReceive('isShuttle')
            ->withNoArgs()
            ->andReturn(false);
        $this->ship->shouldReceive('getStarsystemMap')
            ->withNoArgs()
            ->andReturn($starmap);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $starmap->shouldReceive('getColony')
            ->withNoArgs()
            ->andReturn($colony);

        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $this->colonyLibFactory->shouldReceive('createColonySurface')
            ->with($colony)
            ->andReturn($surface);

        $surface->shouldReceive('hasAirfield')
            ->withNoArgs()
            ->andReturn(true);

        $result = $this->subject->canLandOnCurrentColony();

        $this->assertTrue($result);
    }

    //TODO canBeRetrofitted
    //TODO getTractoringSpacecraftWrapper
    //TODO getDockedToStationWrapper
}
