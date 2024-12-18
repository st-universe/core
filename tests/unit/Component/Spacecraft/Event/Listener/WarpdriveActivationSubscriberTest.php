<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Event\Listener;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\Event\WarpdriveActivationEvent;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\StuTestCase;

class WarpdriveActivationSubscriberTest extends StuTestCase
{
    /** @var MockInterface&TractorMassPayloadUtilInterface */
    private $tractorMassPayloadUtil;
    /** @var MockInterface&SpacecraftStateChangerInterface */
    private $spacecraftStateChanger;
    /** @var MockInterface&ShipUndockingInterface */
    private $shipUndocking;
    /** @var MockInterface&GameControllerInterface */
    private $game;

    /** @var MockInterface&StationInterface */
    private $station;
    /** @var MockInterface&StationWrapperInterface */
    private $wrapper;

    private WarpdriveActivationSubscriber $subject;

    #[Override]
    public function setUp(): void
    {
        $this->tractorMassPayloadUtil = $this->mock(TractorMassPayloadUtilInterface::class);
        $this->spacecraftStateChanger = $this->mock(SpacecraftStateChangerInterface::class);
        $this->shipUndocking = $this->mock(ShipUndockingInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);

        $this->station = $this->mock(StationInterface::class);
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

        $this->spacecraftStateChanger->shouldReceive('changeShipState')
            ->with($this->wrapper, SpacecraftStateEnum::SHIP_STATE_NONE)
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
        $traktoredShip = $this->mock(ShipInterface::class);

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

        $this->spacecraftStateChanger->shouldReceive('changeShipState')
            ->with($this->wrapper, SpacecraftStateEnum::SHIP_STATE_NONE)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($this->station)
            ->once();

        $this->tractorMassPayloadUtil->shouldReceive('tryToTow')
            ->with($this->wrapper, $traktoredShip, $this->game)
            ->once()
            ->andReturnFalse();

        $this->subject->onWarpdriveActivation($event);
    }

    public function testOnWarpdriveActivationExpectSuccessfullTowing(): void
    {
        $event = $this->mock(WarpdriveActivationEvent::class);
        $traktoredShipWrapper = $this->mock(ShipWrapperInterface::class);
        $traktoredShip = $this->mock(ShipInterface::class);

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

        $this->spacecraftStateChanger->shouldReceive('changeShipState')
            ->with($this->wrapper, SpacecraftStateEnum::SHIP_STATE_NONE)
            ->once();
        $this->spacecraftStateChanger->shouldReceive('changeShipState')
            ->with($traktoredShipWrapper, SpacecraftStateEnum::SHIP_STATE_NONE)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($this->station)
            ->once();

        $this->tractorMassPayloadUtil->shouldReceive('tryToTow')
            ->with($this->wrapper, $traktoredShip, $this->game)
            ->once()
            ->andReturnTrue();

        $this->subject->onWarpdriveActivation($event);
    }
}
