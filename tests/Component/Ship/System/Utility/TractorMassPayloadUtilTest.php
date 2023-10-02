<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtil;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageInterface;
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
            $this->stuRandom
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
        $messages = $this->mock(FightMessageCollectionInterface::class);

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
        $messages = $this->mock(FightMessageCollectionInterface::class);

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
        $messages = $this->mock(FightMessageCollectionInterface::class);
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

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 10)
            ->once()
            ->andReturn(1);
        $this->stuRandom->shouldReceive('rand')
            ->with(5, 25)
            ->once()
            ->andReturn($damage);

        $this->applyDamage->shouldReceive('damageShipSystem')
            ->with($this->wrapper, $system, $damage, Mockery::any())
            ->once()
            ->andReturn(false);

        $system->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $message = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (FightMessageInterface $m) use (&$message) {

                $message = $m;
                return true;
            }));

        $this->subject->stressTractorSystemForTowing($this->wrapper, $this->tractoredShip, $messages);

        $this->assertEquals(['Traktoremitter der SHIP ist überbelastet und wurde dadurch beschädigt, Status: 666%'], $message->getMessage());
    }

    public function teststressTractorSystemForTowingExpectFalseWhenOverTresholdAndDestroyed(): void
    {
        $messages = $this->mock(FightMessageCollectionInterface::class);
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
            ->with($this->wrapper, $system, $damage, Mockery::any())
            ->once()
            ->andReturn(true);

        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true)
            ->once()
            ->andReturn(true);

        $message = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (FightMessageInterface $m) use (&$message) {

                $message = $m;
                return true;
            }));

        $this->subject->stressTractorSystemForTowing($this->wrapper, $this->tractoredShip, $messages);

        $this->assertEquals(['Traktoremitter der SHIP wurde zerstört. Die TSHIP wird nicht weiter gezogen'], $message->getMessage());
    }
}
