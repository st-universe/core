<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class RepairConsequenceTest extends StuTestCase
{
    /** @var MockInterface&CancelRepairInterface */
    private MockInterface $cancelRepair;

    private FlightConsequenceInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&FlightRouteInterface */
    private MockInterface $flightRoute;

    protected function setUp(): void
    {
        $this->cancelRepair = $this->mock(CancelRepairInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new RepairConsequence($this->cancelRepair);
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

    public function testTriggerExpectNothingWhenNotUnderRepair(): void
    {
        $messages = $this->mock(FightMessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isUnderRepair')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectCancelWhenUnderRepair(): void
    {
        $messages = $this->mock(FightMessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isUnderRepair')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP');

        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once();

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

        $this->assertEquals(['Die Reparatur der SHIP wurde abgebrochen'], $message->getMessage());
    }
}
