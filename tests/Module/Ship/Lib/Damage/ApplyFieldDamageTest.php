<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Damage;

use Mockery;
use Mockery\MockInterface;
use Stu\Lib\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageFactoryInterface;
use Stu\Module\Ship\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class ApplyFieldDamageTest extends StuTestCase
{
    /** @var MockInterface&ApplyDamageInterface */
    private $applyDamage;
    /** @var MockInterface&ShipDestructionInterface */
    private $shipDestruction;
    /** @var MockInterface|MessageFactoryInterface */
    private $messageFactory;

    private ApplyFieldDamageInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    protected function setUp(): void
    {
        $this->applyDamage = $this->mock(ApplyDamageInterface::class);
        $this->shipDestruction = $this->mock(ShipDestructionInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new ApplyFieldDamage(
            $this->applyDamage,
            $this->shipDestruction,
            $this->messageFactory
        );
    }

    public function testDamageDoesAbsolutDamageToShipAndTractoredShip(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $tractoredShip = $this->mock(ShipInterface::class);
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
        $this->ship->shouldReceive('isDestroyed')
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
        $tractoredShip->shouldReceive('isDestroyed')
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
            ->with(Mockery::on(function (DamageWrapper $damageWrapper): bool {
                return $damageWrapper->getNetDamage() == 42;
            }), $this->wrapper, $message)
            ->once();
        $this->applyDamage->shouldReceive('damage')
            ->with(Mockery::on(function (DamageWrapper $damageWrapper): bool {
                return $damageWrapper->getNetDamage() == 42;
            }), $tractoredShipWrapper, $tmessage)
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
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->applyDamage->shouldReceive('damage')
            ->with(Mockery::on(function (DamageWrapper $damageWrapper): bool {
                return $damageWrapper->getNetDamage() == 100;
            }), $this->wrapper, $message)
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

        $this->shipDestruction->shouldReceive('destroy')
            ->with(
                null,
                $this->wrapper,
                ShipDestructionCauseEnum::FIELD_DAMAGE,
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
