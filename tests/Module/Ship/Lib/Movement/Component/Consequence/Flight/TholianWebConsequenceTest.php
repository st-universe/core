<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Override;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Interaction\TholianWebUtilInterface;
use Stu\Module\Ship\Lib\Message\MessageFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\StuTestCase;

class TholianWebConsequenceTest extends StuTestCase
{
    /** @var MockInterface&TholianWebUtilInterface */
    private $tholianWebUtil;
    /** @var MockInterface|MessageFactoryInterface */
    private $messageFactory;

    private FlightConsequenceInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&FlightRouteInterface */
    private MockInterface $flightRoute;

    #[Override]
    protected function setUp(): void
    {
        $this->tholianWebUtil = $this->mock(TholianWebUtilInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new TholianWebConsequence(
            $this->tholianWebUtil,
            $this->messageFactory
        );
    }

    public function testTriggerExpectNothingWhenShipDestroyed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectNothingWhenNoWeb(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $message = $this->mock(MessageInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SHIPSTATEENUM::SHIP_STATE_NONE);
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with(null, 123)
            ->once()
            ->andReturn($message);


        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectNothingWhenInFinishedWeb(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $web = $this->mock(TholianWebInterface::class);
        $message = $this->mock(MessageInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SHIPSTATEENUM::SHIP_STATE_NONE);
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($web);

        $web->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with(null, 123)
            ->once()
            ->andReturn($message);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectReleaseWhenInUnfinishedWeb(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $web = $this->mock(TholianWebInterface::class);
        $message = $this->mock(MessageInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SHIPSTATEENUM::SHIP_STATE_NONE);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("SHIP");
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($web);

        $web->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with(null, 123)
            ->once()
            ->andReturn($message);

        $message->shouldReceive('add')
            ->with('Die SHIP ist einem unfertigen Energienetz entkommen')
            ->once();

        $this->tholianWebUtil->shouldReceive('releaseShipFromWeb')
            ->with($this->wrapper)
            ->once();


        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectReleaseWhenSpinningWeb(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $message = $this->mock(MessageInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_WEB_SPINNING);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("SHIP");
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with(null, 123)
            ->once()
            ->andReturn($message);

        $message->shouldReceive('add')
            ->with('Die SHIP hat die Unterstützung des Energienetzes abgebrochen')
            ->once();

        $this->tholianWebUtil->shouldReceive('releaseWebHelper')
            ->with($this->wrapper)
            ->once();

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }
}
