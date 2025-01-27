<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SystemDataDeserializerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Ui\StateIconAndTitle;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

class StationWrapperTest extends StuTestCase
{
    /** @var MockInterface&StationInterface */
    private $station;
    /** @var MockInterface&SpacecraftSystemManagerInterface */
    private $spacecraftSystemManager;
    /** @var MockInterface&SystemDataDeserializerInterface */
    private  $systemDataDeserializer;
    /** @var MockInterface&TorpedoTypeRepositoryInterface */
    private  $torpedoTypeRepository;
    /** @var MockInterface&GameControllerInterface */
    private  $game;
    /** @var MockInterface&SpacecraftWrapperFactoryInterface */
    private  $spacecraftWrapperFactory;
    /** @var MockInterface&SpacecraftStateChangerInterface */
    private  $spacecraftStateChanger;
    /** @var MockInterface&RepairUtilInterface */
    private  $repairUtil;
    /** @var MockInterface&StateIconAndTitle */
    private  $stateIconAndTitle;

    private StationWrapperInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->station = $this->mock(StationInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->systemDataDeserializer = $this->mock(SystemDataDeserializerInterface::class);
        $this->torpedoTypeRepository = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->spacecraftStateChanger = $this->mock(SpacecraftStateChangerInterface::class);
        $this->repairUtil = $this->mock(RepairUtilInterface::class);
        $this->stateIconAndTitle = $this->mock(StateIconAndTitle::class);

        $this->subject = new StationWrapper(
            $this->station,
            $this->spacecraftSystemManager,
            $this->systemDataDeserializer,
            $this->torpedoTypeRepository,
            $this->game,
            $this->spacecraftWrapperFactory,
            $this->spacecraftStateChanger,
            $this->repairUtil,
            $this->stateIconAndTitle
        );
    }

    public function testCanBeScrappedExpectTrueWhenNotUnderScrapping(): void
    {
        $this->station->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::UNDER_CONSTRUCTION);

        $result = $this->subject->canBeScrapped();

        $this->assertTrue($result);
    }

    public function testCanBeScrappedExpectTrueWhenAlreadyScrapping(): void
    {
        $this->station->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::UNDER_SCRAPPING);

        $result = $this->subject->canBeScrapped();

        $this->assertFalse($result);
    }
}
