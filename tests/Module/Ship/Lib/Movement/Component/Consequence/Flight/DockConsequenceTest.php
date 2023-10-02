<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Mockery;
use Mockery\MockInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class DockConsequenceTest extends StuTestCase
{
    private FlightConsequenceInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&FlightRouteInterface */
    private MockInterface $flightRoute;

    protected function setUp(): void
    {
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new DockConsequence();
    }

    public function testTriggerExpectNothingWhenShipDestroyed(): void
    {
        $messages = $this->mock(FightMessageCollectionInterface::class);

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

    public function testTriggerExpectNothingWhenNotDocked(): void
    {
        $messages = $this->mock(FightMessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectUndockingWhenShipDocked(): void
    {
        $messages = $this->mock(FightMessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(ShipInterface::class));
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $message = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (FightMessageInterface $m) use (&$message) {

                if ($m->getRecipientId() === 123) {
                    $message = $m;
                    return true;
                }

                return false;
            }));

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );

        $this->assertEquals(['Die SHIP wurde abgedockt'], $message->getMessage());
    }
    public function testTriggerExpectUndockingWhenShipDockedAndTractored(): void
    {
        $messages = $this->mock(FightMessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(ShipInterface::class));
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $message = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (FightMessageInterface $m) use (&$message) {

                if ($m->getRecipientId() === 123) {
                    $message = $m;
                    return true;
                }

                return false;
            }));

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );

        $this->assertEquals(['Die SHIP wurde abgedockt'], $message->getMessage());
    }
}
