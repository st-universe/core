<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Event\Listener;

use Mockery\MockInterface;
use Override;
use Stu\Component\Ship\Event\WarpdriveActivationEvent;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class WarpdriveActivationSubscriberTest extends StuTestCase
{
    /** @var MockInterface&TractorMassPayloadUtilInterface */
    private $tractorMassPayloadUtil;
    /** @var MockInterface&ShipStateChangerInterface */
    private $shipStateChanger;
    /** @var MockInterface&ShipUndockingInterface */
    private $shipUndocking;
    /** @var MockInterface&GameControllerInterface */
    private $game;

    /** @var MockInterface&ShipInterface */
    private $ship;
    /** @var MockInterface&ShipWrapperInterface */
    private $wrapper;

    private WarpdriveActivationSubscriber $subject;

    #[Override]
    public function setUp(): void
    {
        $this->tractorMassPayloadUtil = $this->mock(TractorMassPayloadUtilInterface::class);
        $this->shipStateChanger = $this->mock(ShipStateChangerInterface::class);
        $this->shipUndocking = $this->mock(ShipUndockingInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new WarpdriveActivationSubscriber(
            $this->tractorMassPayloadUtil,
            $this->shipStateChanger,
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

        $this->shipStateChanger->shouldReceive('changeShipState')
            ->with($this->wrapper, ShipStateEnum::SHIP_STATE_NONE)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($this->ship)
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

        $this->shipStateChanger->shouldReceive('changeShipState')
            ->with($this->wrapper, ShipStateEnum::SHIP_STATE_NONE)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($this->ship)
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

        $this->shipStateChanger->shouldReceive('changeShipState')
            ->with($this->wrapper, ShipStateEnum::SHIP_STATE_NONE)
            ->once();
        $this->shipStateChanger->shouldReceive('changeShipState')
            ->with($traktoredShipWrapper, ShipStateEnum::SHIP_STATE_NONE)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($this->ship)
            ->once();

        $this->tractorMassPayloadUtil->shouldReceive('tryToTow')
            ->with($this->wrapper, $traktoredShip, $this->game)
            ->once()
            ->andReturnTrue();

        $this->subject->onWarpdriveActivation($event);
    }
}
