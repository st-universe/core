<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\Utility\TractorMassPayloadUtil;
use Stu\Component\Spacecraft\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Damage\SystemDamageInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\StuTestCase;

class TractorMassPayloadUtilTest extends StuTestCase
{
    private MockInterface&SystemDamageInterface $systemDamage;
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;
    private MockInterface&StuRandom $stuRandom;
    private MockInterface&MessageFactoryInterface $messageFactory;

    private MockInterface&Ship $ship;
    private MockInterface&Ship $tractoredShip;
    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&InformationInterface $information;

    private TractorMassPayloadUtilInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //INJECTED
        $this->systemDamage = $this->mock(SystemDamageInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        //PARAMS
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(Ship::class);
        $this->tractoredShip = $this->mock(Ship::class);
        $this->information = $this->mock(InformationInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new class(
            $this->systemDamage,
            $this->spacecraftSystemManager,
            $this->stuRandom,
            $this->messageFactory
        ) extends TractorMassPayloadUtil {

            #[Override]
            protected function getTractorPayload(Spacecraft $spacecraft): int
            {
                return $spacecraft->getRump()->getTractorPayload();
            }
        };
    }


    public function testTryToTowExpectReleaseWhenTargetInOtherFleetWithMoreThanOneShip(): void
    {
        $tractoredShipFleet = $this->mock(Fleet::class);

        $this->ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(Fleet::class));

        $this->tractoredShip->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredShipFleet);
        $this->tractoredShip->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("TSHIP");

        $tractoredShipFleet->shouldReceive('getShipCount')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true)
            ->once();

        $this->information->shouldReceive('addInformationf')
            ->with('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde deaktiviert', 'TSHIP')
            ->once();

        $result = $this->subject->tryToTow($this->wrapper, $this->tractoredShip, $this->information);

        $this->assertFalse($result);
    }

    public function testTryToTowExpectDeactivationWhenToHeavy(): void
    {
        $tractoredShipFleet = $this->mock(Fleet::class);

        $this->ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(Fleet::class));
        $this->ship->shouldReceive('getRump->getTractorPayload')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP');

        $this->tractoredShip->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredShipFleet);
        $this->tractoredShip->shouldReceive('getRump->getTractorMass')
            ->withNoArgs()
            ->once()
            ->andReturn(43);
        $this->tractoredShip->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('TSHIP');

        $tractoredShipFleet->shouldReceive('getShipCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true)
            ->once();

        $this->information->shouldReceive('addInformationf')
            ->with('Traktoremitter der %s war nicht leistungsstark genug um die %s zu ziehen und wurde daher deaktiviert', 'SHIP', 'TSHIP')
            ->once();

        $result = $this->subject->tryToTow($this->wrapper, $this->tractoredShip, $this->information);

        $this->assertFalse($result);
    }

    public function testTryToTowExpectSuccessWhenPotentEnough(): void
    {
        $fleet = $this->mock(Fleet::class);

        $this->ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($fleet);

        $this->tractoredShip->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($fleet);

        $this->ship->shouldReceive('getRump->getTractorPayload')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->tractoredShip->shouldReceive('getRump->getTractorMass')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->subject->tryToTow($this->wrapper, $this->tractoredShip, $this->information);

        $this->assertTrue($result);
    }

    public function teststressTractorSystemForTowingExpectTrueWhenThresholdReached(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('getRump->getTractorPayload')
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

        $this->ship->shouldReceive('getRump->getTractorPayload')
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
        $system = $this->mock(SpacecraftSystem::class);
        $message = $this->mock(MessageInterface::class);

        $damage = 7;

        $this->ship->shouldReceive('getRump->getTractorPayload')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TRACTOR_BEAM)
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

        $this->systemDamage->shouldReceive('damageShipSystem')
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
        $system = $this->mock(SpacecraftSystem::class);

        $damage = 7;

        $this->ship->shouldReceive('getRump->getTractorPayload')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TRACTOR_BEAM)
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

        $this->systemDamage->shouldReceive('damageShipSystem')
            ->with($this->wrapper, $system, $damage, $message)
            ->once()
            ->andReturn(true);

        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true)
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
