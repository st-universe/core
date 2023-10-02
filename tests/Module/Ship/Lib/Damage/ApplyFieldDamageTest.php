<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Damage;

use Mockery;
use Mockery\MockInterface;
use Stu\Lib\DamageWrapper;
use Stu\Lib\InformationWrapper;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class ApplyFieldDamageTest extends StuTestCase
{
    /** @var MockInterface&ApplyDamageInterface */
    private MockInterface $applyDamage;

    /** @var MockInterface&EntryCreatorInterface */
    private MockInterface $entryCreator;

    /** @var MockInterface&ShipRemoverInterface */
    private MockInterface $shipRemover;

    private ApplyFieldDamageInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    protected function setUp(): void
    {
        $this->applyDamage = $this->mock(ApplyDamageInterface::class);
        $this->entryCreator = $this->mock(EntryCreatorInterface::class);
        $this->shipRemover = $this->mock(ShipRemoverInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new ApplyFieldDamage(
            $this->applyDamage,
            $this->entryCreator,
            $this->shipRemover
        );
    }

    public function testDamageDoesAbsolutDamageToShipAndTractoredShip(): void
    {
        $messages = $this->mock(FightMessageCollectionInterface::class);
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
        $this->ship->shouldReceive('getRump->getName')
            ->withNoArgs()
            ->once()
            ->andReturn("RUMP");
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
        $tractoredShip->shouldReceive('getRump->getName')
            ->withNoArgs()
            ->once()
            ->andReturn("TRUMP");
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
            ->with(Mockery::on(function (FightMessageInterface $m) use (&$message) {

                if ($m->getRecipientId() === 666) {
                    $message = $m;
                    return true;
                }

                return false;
            }));

        $Tmessage = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (FightMessageInterface $m) use (&$Tmessage) {

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
        $messages = $this->mock(FightMessageCollectionInterface::class);
        $informations = $this->mock(InformationWrapper::class);

        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("SHIP");
        $this->ship->shouldReceive('getRump->getName')
            ->withNoArgs()
            ->once()
            ->andReturn("RUMP");
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
        $this->ship->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn("SECTOR");

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
            ->with(Mockery::on(function (FightMessageInterface $m) use (&$message) {

                $message = $m;

                return $m->getRecipientId() === 666;
            }));

        $this->entryCreator->shouldReceive('addShipEntry')
            ->with('Die SHIP (RUMP) wurde beim Einflug in Sektor SECTOR zerstört')
            ->once();

        $this->shipRemover->shouldReceive('destroy')
            ->with($this->wrapper)
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
