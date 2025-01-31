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
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\Ui\StateIconAndTitle;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

class ShipWrapperTest extends StuTestCase
{
    /** @var MockInterface&ShipInterface */
    private $ship;
    /** @var MockInterface&SpacecraftSystemManagerInterface */
    private $spacecraftSystemManager;
    /** @var MockInterface&SystemDataDeserializerInterface */
    private $systemDataDeserializer;
    /** @var MockInterface&TorpedoTypeRepositoryInterface */
    private $torpedoTypeRepository;
    /** @var MockInterface&GameControllerInterface */
    private $game;
    /** @var MockInterface&SpacecraftWrapperFactoryInterface */
    private $spacecraftWrapperFactory;
    /** @var MockInterface&SpacecraftStateChangerInterface */
    private $spacecraftStateChanger;
    /** @var MockInterface&RepairUtilInterface */
    private $repairUtil;
    /** @var MockInterface&StateIconAndTitle */
    private $stateIconAndTitle;
    /** @var MockInterface&ColonyLibFactoryInterface */
    private $colonyLibFactory;

    private ShipWrapperInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->ship = $this->mock(ShipInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->systemDataDeserializer = $this->mock(SystemDataDeserializerInterface::class);
        $this->torpedoTypeRepository = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
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
            ->andReturn($this->mock(CommodityInterface::class));
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
            ->andReturn($this->mock(CommodityInterface::class));
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
        $starmap = $this->mock(StarSystemMapInterface::class);

        $this->ship->shouldReceive('getRump->getCommodity')
            ->withNoArgs()
            ->andReturn($this->mock(CommodityInterface::class));
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
        $user = $this->mock(UserInterface::class);
        $starmap = $this->mock(StarSystemMapInterface::class);
        $colony = $this->mock(ColonyInterface::class);

        $this->ship->shouldReceive('getRump->getCommodity')
            ->withNoArgs()
            ->andReturn($this->mock(CommodityInterface::class));
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
            ->andReturn($this->mock(UserInterface::class));

        $result = $this->subject->canLandOnCurrentColony();

        $this->assertFalse($result);
    }

    public function testCanLandOnCurrentColonyExpectFalseWhenNoAirfieldOnColony(): void
    {
        $user = $this->mock(UserInterface::class);
        $starmap = $this->mock(StarSystemMapInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $surface = $this->mock(ColonySurfaceInterface::class);

        $this->ship->shouldReceive('getRump->getCommodity')
            ->withNoArgs()
            ->andReturn($this->mock(CommodityInterface::class));
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
        $user = $this->mock(UserInterface::class);
        $starmap = $this->mock(StarSystemMapInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $surface = $this->mock(ColonySurfaceInterface::class);

        $this->ship->shouldReceive('getRump->getCommodity')
            ->withNoArgs()
            ->andReturn($this->mock(CommodityInterface::class));
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
