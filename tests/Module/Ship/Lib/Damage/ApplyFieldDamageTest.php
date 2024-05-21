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
use Stu\Module\Ship\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class ApplyFieldDamageTest extends StuTestCase
{
    /** @var MockInterface&ApplyDamageInterface */
    private MockInterface $applyDamage;

    /** @var MockInterface&ShipDestructionInterface */
    private MockInterface $shipDestruction;

    private ApplyFieldDamageInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    protected function setUp(): void
    {
        $this->applyDamage = $this->mock(ApplyDamageInterface::class);
        $this->shipDestruction = $this->mock(ShipDestructionInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new ApplyFieldDamage(
            $this->applyDamage,
            $this->shipDestruction
        );
    }

    public function testDamageDoesAbsolutDamageToShipAndTractoredShip(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $informations = $this->mock(InformationWrapper::class);
        $Tinformations = $this->mock(InformationWrapper::class);

        $tractoredShip = $this->mock(ShipInterface::class);
        $tractoredShipWrapper = $this->mock(ShipWrapperInterface::class);

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
            ->with(Mockery::on(function (DamageWrapper $damageWrapper) {
                return $damageWrapper->getNetDamage() == 42;
            }), $this->wrapper)
            ->once()
            ->andReturn($informations);
        $this->applyDamage->shouldReceive('damage')
            ->with(Mockery::on(function (DamageWrapper $damageWrapper) {
                return $damageWrapper->getNetDamage() == 42;
            }), $tractoredShipWrapper)
            ->once()
            ->andReturn($Tinformations);

        $informations->shouldReceive('getInformations')
            ->withNoArgs()
            ->once()
            ->andReturn(['APPLY_DAMAGE_INFOS']);
        $Tinformations->shouldReceive('getInformations')
            ->withNoArgs()
            ->once()
            ->andReturn(['T_APPLY_DAMAGE_INFOS']);

        $message = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (MessageInterface $m) use (&$message) {

                if ($m->getRecipientId() === 666) {
                    $message = $m;
                    return true;
                }

                return false;
            }));

        $Tmessage = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (MessageInterface $m) use (&$Tmessage) {

                if ($m->getRecipientId() === 667) {
                    $Tmessage = $m;
                    return true;
                }

                return false;
            }));

        $this->subject->damage(
            $this->wrapper,
            42,
            true,
            'CAUSE',
            $messages
        );

        $this->assertEquals([
            'CAUSE: Die SHIP wurde in Sektor 22|33 beschädigt',
            'APPLY_DAMAGE_INFOS'
        ], $message->getMessage());

        $this->assertEquals([
            'CAUSE: Die TSHIP wurde in Sektor 23|34 beschädigt',
            'T_APPLY_DAMAGE_INFOS'
        ], $Tmessage->getMessage());
    }

    public function testDamageDoesPercentageDamageToShipAndDestroys(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $informations = $this->mock(InformationWrapper::class);

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
            ->with(Mockery::on(function (DamageWrapper $damageWrapper) {
                return $damageWrapper->getNetDamage() == 100;
            }), $this->wrapper)
            ->once()
            ->andReturn($informations);

        $informations->shouldReceive('getInformations')
            ->withNoArgs()
            ->once()
            ->andReturn(['APPLY_DAMAGE_INFOS']);

        $message = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (MessageInterface $m) use (&$message) {

                $message = $m;

                return $m->getRecipientId() === 666;
            }));

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

        $this->assertEquals([
            'CAUSE: Die SHIP wurde in Sektor 22|33 beschädigt',
            'APPLY_DAMAGE_INFOS'
        ], $message->getMessage());
    }
}
