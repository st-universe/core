<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

use Mockery\MockInterface;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtil;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Lib\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
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

    public function testTractorSystemSurvivedTowingExpectTrueWhenThresholdReached(): void
    {
        $informationWrapper = $this->mock(InformationWrapper::class);

        $this->ship->shouldReceive('getTractorPayload')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->tractoredShip->shouldReceive('getRump->getTractorMass')
            ->withNoArgs()
            ->once()
            ->andReturn(90);

        $result = $this->subject->tractorSystemSurvivedTowing($this->wrapper, $this->tractoredShip, $informationWrapper);

        $this->assertTrue($result);
    }

    public function testTractorSystemSurvivedTowingExpectTrueWhenOverTresholdButRandomMissed(): void
    {
        $informationWrapper = $this->mock(InformationWrapper::class);

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

        $result = $this->subject->tractorSystemSurvivedTowing($this->wrapper, $this->tractoredShip, $informationWrapper);

        $this->assertTrue($result);
    }

    public function testTractorSystemSurvivedTowingExpectTrueWhenOverTresholdAndStillHealthy(): void
    {
        $informationWrapper = $this->mock(InformationWrapper::class);
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
            ->with($this->wrapper, $system, $damage, $informationWrapper)
            ->once()
            ->andReturn(false);

        $system->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $informationWrapper->shouldReceive('addInformation')
            ->with(
                'Traktoremitter der SHIP ist überbelastet und wurde dadurch beschädigt, Status: 666%',
            )
            ->once();

        $result = $this->subject->tractorSystemSurvivedTowing($this->wrapper, $this->tractoredShip, $informationWrapper);

        $this->assertTrue($result);
    }

    public function testTractorSystemSurvivedTowingExpectFalseWhenOverTresholdAndDestroyed(): void
    {
        $informationWrapper = $this->mock(InformationWrapper::class);
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
            ->with($this->wrapper, $system, $damage, $informationWrapper)
            ->once()
            ->andReturn(true);

        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true)
            ->once()
            ->andReturn(true);

        $informationWrapper->shouldReceive('addInformation')
            ->with(
                'Traktoremitter der SHIP wurde zerstört. Die TSHIP wird nicht weiter gezogen',
            )
            ->once();

        $result = $this->subject->tractorSystemSurvivedTowing($this->wrapper, $this->tractoredShip, $informationWrapper);

        $this->assertFalse($result);
    }
}
