<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtil;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageFactoryInterface;
use Stu\Module\Ship\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\StuTestCase;

class TractorMassPayloadUtilTest extends StuTestCase
{
    /** @var MockInterface|ApplyDamageInterface */
    private $applyDamage;
    /** @var MockInterface|ShipSystemManagerInterface */
    private $shipSystemManager;
    /** @var MockInterface|StuRandom */
    private $stuRandom;
    /** @var MockInterface|MessageFactoryInterface */
    private $messageFactory;

    /** @var MockInterface|ShipInterface */
    private $ship;

    /** @var MockInterface|ShipInterface */
    private $tractoredShip;

    /** @var MockInterface|ShipWrapperInterface */
    private $wrapper;

    private TractorMassPayloadUtilInterface $subject;

    public function setUp(): void
    {
        //INJECTED
        $this->applyDamage = $this->mock(ApplyDamageInterface::class);
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        //PARAMS
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(ShipInterface::class);
        $this->tractoredShip = $this->mock(ShipInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new TractorMassPayloadUtil(
            $this->applyDamage,
            $this->shipSystemManager,
            $this->stuRandom,
            $this->messageFactory
        );
    }

    public function testTryToTowExpectDeactivationWhenToHeavy(): void
    {
        $this->ship->shouldReceive('getTractorPayload')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP');

        $this->tractoredShip->shouldReceive('getRump->getTractorMass')
            ->withNoArgs()
            ->once()
            ->andReturn(43);
        $this->tractoredShip->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('TSHIP');

        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true)
            ->once();

        $reason = $this->subject->tryToTow($this->wrapper, $this->tractoredShip);

        $this->assertEquals('Traktoremitter der SHIP war nicht stark genug um die TSHIP zu ziehen und wurde daher deaktiviert', $reason);
    }

    public function testTryToTowExpectSuccessWhenPotentEnough(): void
    {
        $this->ship->shouldReceive('getTractorPayload')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->tractoredShip->shouldReceive('getRump->getTractorMass')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $reason = $this->subject->tryToTow($this->wrapper, $this->tractoredShip);

        $this->assertNull($reason);
    }

    public function teststressTractorSystemForTowingExpectTrueWhenThresholdReached(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('getTractorPayload')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->tractoredShip->shouldReceive('getRump->getTractorMass')
            ->withNoArgs()
            ->once()
            ->andReturn(90);

        $this->subject->stressTractorSystemForTowing($this->wrapper, $this->tractoredShip, $messages);
    }

    public function teststressTractorSystemForTowingExpectTrueWhenOverTresholdButRandomMissed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('getTractorPayload')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->tractoredShip->shouldReceive('getRump->getTractorMass')
            ->withNoArgs()
            ->once()
            ->andReturn(91);

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 10)
            ->once()
            ->andReturn(2);

        $this->subject->stressTractorSystemForTowing($this->wrapper, $this->tractoredShip, $messages);
    }

    public function teststressTractorSystemForTowingExpectTrueWhenOverTresholdAndStillHealthy(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $system = $this->mock(ShipSystemInterface::class);
        $message = $this->mock(MessageInterface::class);

        $damage = 7;

        $this->ship->shouldReceive('getTractorPayload')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)
            ->once()
            ->andReturn($system);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP');

        $this->tractoredShip->shouldReceive('getRump->getTractorMass')
            ->withNoArgs()
            ->once()
            ->andReturn(91);

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 10)
            ->once()
            ->andReturn(1);
        $this->stuRandom->shouldReceive('rand')
            ->with(5, 25)
            ->once()
            ->andReturn($damage);

        $this->applyDamage->shouldReceive('damageShipSystem')
            ->with($this->wrapper, $system, $damage, $message)
            ->once()
            ->andReturn(false);

        $system->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $message->shouldReceive('addInformation')
            ->with('Traktoremitter der SHIP ist überbelastet und wurde dadurch beschädigt, Status: 666%')
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->withNoArgs()
            ->once()
            ->andReturn($message);

        $this->subject->stressTractorSystemForTowing($this->wrapper, $this->tractoredShip, $messages);
    }

    public function teststressTractorSystemForTowingExpectFalseWhenOverTresholdAndDestroyed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $message = $this->mock(MessageInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $damage = 7;

        $this->ship->shouldReceive('getTractorPayload')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)
            ->once()
            ->andReturn($system);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP');

        $this->tractoredShip->shouldReceive('getRump->getTractorMass')
            ->withNoArgs()
            ->once()
            ->andReturn(91);
        $this->tractoredShip->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('TSHIP');

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 10)
            ->once()
            ->andReturn(1);
        $this->stuRandom->shouldReceive('rand')
            ->with(5, 25)
            ->once()
            ->andReturn($damage);

        $this->applyDamage->shouldReceive('damageShipSystem')
            ->with($this->wrapper, $system, $damage, $message)
            ->once()
            ->andReturn(true);

        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true)
            ->once()
            ->andReturn(true);

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $message->shouldReceive('addInformation')
            ->with('Traktoremitter der SHIP wurde zerstört. Die TSHIP wird nicht weiter gezogen')
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->withNoArgs()
            ->once()
            ->andReturn($message);

        $this->subject->stressTractorSystemForTowing($this->wrapper, $this->tractoredShip, $messages);
    }
}
