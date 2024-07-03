<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Override;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Module\Ship\Lib\Message\MessageFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class TractorConsequenceTest extends StuTestCase
{
    /** @var MockInterface&TractorMassPayloadUtilInterface */
    private $tractorMassPayloadUtil;
    /** @var MockInterface&ShipSystemManagerInterface */
    private $shipSystemManager;
    /** @var MockInterface&CancelColonyBlockOrDefendInterface */
    private $cancelColonyBlockOrDefend;
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
        $this->tractorMassPayloadUtil = $this->mock(TractorMassPayloadUtilInterface::class);
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->cancelColonyBlockOrDefend = $this->mock(CancelColonyBlockOrDefendInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new TractorConsequence(
            $this->tractorMassPayloadUtil,
            $this->shipSystemManager,
            $this->cancelColonyBlockOrDefend,
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

    public function testTriggerExpectReleaseWhenTargetInFleetWithMoreThanOneShip(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $tractoredShip = $this->mock(ShipInterface::class);
        $tractoredShipFleet = $this->mock(FleetInterface::class);
        $message = $this->mock(MessageInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredShip);

        $tractoredShip->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredShipFleet);
        $tractoredShip->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("TSHIP");

        $tractoredShipFleet->shouldReceive('getShipCount')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true)
            ->once();

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with()
            ->once()
            ->andReturn($message);

        $message->shouldReceive('add')
            ->with('Flottenschiffe können nicht mitgezogen werden - Der auf die TSHIP gerichtete Traktorstrahl wurde deaktiviert')
            ->once();

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectReleaseWhenTargetCantBeTowed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $tractoredShip = $this->mock(ShipInterface::class);
        $message = $this->mock(MessageInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredShip);

        $tractoredShip->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->tractorMassPayloadUtil->shouldReceive('tryToTow')
            ->with($this->wrapper, $tractoredShip)
            ->once()
            ->andReturn('ABORT');

        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true)
            ->once();

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with()
            ->once()
            ->andReturn($message);

        $message->shouldReceive('add')
            ->with('ABORT')
            ->once();

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectColonyBlockDefendCallWhenCanBeTowed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $tractoredShip = $this->mock(ShipInterface::class);
        $message = $this->mock(MessageInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredShip);

        $tractoredShip->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->tractorMassPayloadUtil->shouldReceive('tryToTow')
            ->with($this->wrapper, $tractoredShip)
            ->once()
            ->andReturn(null);

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->withNoArgs()
            ->once()
            ->andReturn($message);

        $this->cancelColonyBlockOrDefend->shouldReceive('work')
            ->with($this->ship, $message, true)
            ->once();


        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }
}
