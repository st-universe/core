<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class TractorConsequenceTest extends StuTestCase
{
    /** @var MockInterface&TractorMassPayloadUtilInterface */
    private MockInterface $tractorMassPayloadUtil;

    /** @var MockInterface&ShipSystemManagerInterface */
    private MockInterface $shipSystemManager;

    /** @var MockInterface&CancelColonyBlockOrDefendInterface */
    private MockInterface $cancelColonyBlockOrDefend;

    private FlightConsequenceInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&FlightRouteInterface */
    private MockInterface $flightRoute;

    protected function setUp(): void
    {
        $this->tractorMassPayloadUtil = $this->mock(TractorMassPayloadUtilInterface::class);
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->cancelColonyBlockOrDefend = $this->mock(CancelColonyBlockOrDefendInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new TractorConsequence(
            $this->tractorMassPayloadUtil,
            $this->shipSystemManager,
            $this->cancelColonyBlockOrDefend
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

        $message = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (MessageInterface $m) use (&$message) {

                $message = $m;
                return true;
            }));

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );

        $this->assertEquals(
            ['Flottenschiffe können nicht mitgezogen werden - Der auf die TSHIP gerichtete Traktorstrahl wurde deaktiviert'],
            $message->getMessage()
        );
    }

    public function testTriggerExpectReleaseWhenTargetCantBeTowed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $tractoredShip = $this->mock(ShipInterface::class);

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

        $message = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (MessageInterface $m) use (&$message) {

                $message = $m;
                return true;
            }));

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );

        $this->assertEquals(
            ['ABORT'],
            $message->getMessage()
        );
    }

    public function testTriggerExpectColonyBlockDefendCallWhenCanBeTowed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $tractoredShip = $this->mock(ShipInterface::class);

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

        $message = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (MessageInterface $m) use (&$message) {

                $message = $m;
                return true;
            }));

        $informations = null;
        $this->cancelColonyBlockOrDefend->shouldReceive('work')
            ->with($this->ship, Mockery::on(function (InformationWrapper $w) use (&$informations) {

                $informations = $w;
                $informations->addInformation('HIT!');
                return true;
            }), true)
            ->once();


        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );

        $this->assertEquals(['HIT!'], $message->getMessage());
    }
}
