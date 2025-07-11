<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Damage;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class ApplyFieldDamageTest extends StuTestCase
{
    private MockInterface&ApplyDamageInterface $applyDamage;
    private MockInterface&SpacecraftDestructionInterface $spacecraftDestruction;
    private MockInterface&MessageFactoryInterface $messageFactory;

    private ApplyFieldDamageInterface $subject;

    private MockInterface&Ship $ship;

    private MockInterface&ShipWrapperInterface $wrapper;

    #[Override]
    protected function setUp(): void
    {
        $this->applyDamage = $this->mock(ApplyDamageInterface::class);
        $this->spacecraftDestruction = $this->mock(SpacecraftDestructionInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->ship = $this->mock(Ship::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new ApplyFieldDamage(
            $this->applyDamage,
            $this->spacecraftDestruction,
            $this->messageFactory
        );
    }

    public function testDamageDoesAbsolutDamageToShipAndTractoredShip(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $tractoredShip = $this->mock(Ship::class);
        $tractoredShipWrapper = $this->mock(ShipWrapperInterface::class);

        $message = $this->mock(MessageInterface::class);
        $tmessage = $this->mock(MessageInterface::class);

        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("SHIP");
        $this->ship->shouldReceive('getPosX')
            ->withNoArgs()
            ->once()
            ->andReturn(22);
        $this->ship->shouldReceive('getPosY')
            ->withNoArgs()
            ->once()
            ->andReturn(33);
        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $tractoredShip->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(667);
        $tractoredShip->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("TSHIP");
        $tractoredShip->shouldReceive('getPosX')
            ->withNoArgs()
            ->once()
            ->andReturn(23);
        $tractoredShip->shouldReceive('getPosY')
            ->withNoArgs()
            ->once()
            ->andReturn(34);
        $tractoredShip->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredShipWrapper);

        $tractoredShipWrapper->shouldReceive('get')
            ->once()
            ->andReturn($tractoredShip);

        $this->applyDamage->shouldReceive('damage')
            ->with(Mockery::on(fn(DamageWrapper $damageWrapper): bool => $damageWrapper->getNetDamage() == 42), $this->wrapper, $message)
            ->once();
        $this->applyDamage->shouldReceive('damage')
            ->with(Mockery::on(fn(DamageWrapper $damageWrapper): bool => $damageWrapper->getNetDamage() == 42), $tractoredShipWrapper, $tmessage)
            ->once();

        $messages->shouldReceive('add')
            ->with($message)
            ->once();
        $messages->shouldReceive('add')
            ->with($tmessage)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with(null, 666)
            ->once()
            ->andReturn($message);
        $this->messageFactory->shouldReceive('createMessage')
            ->with(null, 667)
            ->once()
            ->andReturn($tmessage);

        $message->shouldReceive('add')
            ->with('CAUSE: Die SHIP wurde in Sektor 22|33 beschädigt')
            ->once();
        $tmessage->shouldReceive('add')
            ->with('CAUSE: Die TSHIP wurde in Sektor 23|34 beschädigt')
            ->once();

        $this->subject->damage(
            $this->wrapper,
            42,
            true,
            'CAUSE',
            $messages
        );
    }

    public function testDamageDoesPercentageDamageToShipAndDestroys(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $message = $this->mock(MessageInterface::class);

        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("SHIP");
        $this->ship->shouldReceive('getPosX')
            ->withNoArgs()
            ->once()
            ->andReturn("22");
        $this->ship->shouldReceive('getPosY')
            ->withNoArgs()
            ->once()
            ->andReturn("33");
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()
            ->once()
            ->andReturn(1000);
        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->applyDamage->shouldReceive('damage')
            ->with(Mockery::on(fn(DamageWrapper $damageWrapper): bool => $damageWrapper->getNetDamage() == 100), $this->wrapper, $message)
            ->once();

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with(null, 666)
            ->once()
            ->andReturn($message);

        $message->shouldReceive('add')
            ->with('CAUSE: Die SHIP wurde in Sektor 22|33 beschädigt')
            ->once();

        $this->spacecraftDestruction->shouldReceive('destroy')
            ->with(
                null,
                $this->wrapper,
                SpacecraftDestructionCauseEnum::FIELD_DAMAGE,
                Mockery::any()
            )
            ->once();

        $this->subject->damage(
            $this->wrapper,
            10,
            false,
            'CAUSE',
            $messages
        );
    }
}
