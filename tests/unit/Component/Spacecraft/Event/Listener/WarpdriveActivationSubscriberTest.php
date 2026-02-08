<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Event\Listener;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\Event\WarpdriveActivationEvent;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\StuTestCase;

class WarpdriveActivationSubscriberTest extends StuTestCase
{
    private MockInterface&TractorMassPayloadUtilInterface $tractorMassPayloadUtil;
    private MockInterface&SpacecraftStateChangerInterface $spacecraftStateChanger;
    private MockInterface&ShipUndockingInterface $shipUndocking;
    private MockInterface&GameControllerInterface $game;

    private MockInterface&Station $station;
    private MockInterface&StationWrapperInterface $wrapper;

    private WarpdriveActivationSubscriber $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->tractorMassPayloadUtil = $this->mock(TractorMassPayloadUtilInterface::class);
        $this->spacecraftStateChanger = $this->mock(SpacecraftStateChangerInterface::class);
        $this->shipUndocking = $this->mock(ShipUndockingInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);

        $this->station = $this->mock(Station::class);
        $this->wrapper = $this->mock(StationWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->station);

        $this->subject = new WarpdriveActivationSubscriber(
            $this->tractorMassPayloadUtil,
            $this->spacecraftStateChanger,
            $this->shipUndocking,
            $this->game
        );
    }

    public function testOnWarpdriveActivationExpectNoTractoringStuff(): void
    {
        $event = $this->mock(WarpdriveActivationEvent::class);

        $event->shouldReceive('getWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($this->wrapper);

        $this->wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->spacecraftStateChanger->shouldReceive('changeState')
            ->with($this->wrapper, SpacecraftStateEnum::NONE)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($this->station)
            ->once();

        $this->subject->onWarpdriveActivation($event);
    }

    public function testOnWarpdriveActivationExpectTryToTowWhenTractoring(): void
    {
        $event = $this->mock(WarpdriveActivationEvent::class);
        $traktoredShipWrapper = $this->mock(ShipWrapperInterface::class);
        $traktoredShip = $this->mock(Ship::class);
        $info = $this->mock(InformationWrapper::class);

        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);

        $event->shouldReceive('getWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($this->wrapper);

        $this->wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($traktoredShipWrapper);

        $traktoredShipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($traktoredShip);

        $this->spacecraftStateChanger->shouldReceive('changeState')
            ->with($this->wrapper, SpacecraftStateEnum::NONE)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($this->station)
            ->once();

        $this->tractorMassPayloadUtil->shouldReceive('tryToTow')
            ->with($this->wrapper, $traktoredShip, $info)
            ->once()
            ->andReturnFalse();

        $this->subject->onWarpdriveActivation($event);
    }

    public function testOnWarpdriveActivationExpectSuccessfullTowing(): void
    {
        $event = $this->mock(WarpdriveActivationEvent::class);
        $traktoredShipWrapper = $this->mock(ShipWrapperInterface::class);
        $traktoredShip = $this->mock(Ship::class);
        $info = $this->mock(InformationWrapper::class);

        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);

        $event->shouldReceive('getWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($this->wrapper);

        $this->wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($traktoredShipWrapper);

        $traktoredShipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($traktoredShip);

        $this->spacecraftStateChanger->shouldReceive('changeState')
            ->with($this->wrapper, SpacecraftStateEnum::NONE)
            ->once();
        $this->spacecraftStateChanger->shouldReceive('changeState')
            ->with($traktoredShipWrapper, SpacecraftStateEnum::NONE)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($this->station)
            ->once();

        $this->tractorMassPayloadUtil->shouldReceive('tryToTow')
            ->with($this->wrapper, $traktoredShip, $info)
            ->once()
            ->andReturnTrue();

        $this->subject->onWarpdriveActivation($event);
    }
}
