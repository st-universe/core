<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Provider;

use Mockery\MockInterface;
use RuntimeException;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ShipAttackerTest extends StuTestCase
{
    /**
     * @var MockInterface|ShipWrapperInterface
     */
    private $wrapper;

    /**
     * @var MockInterface|ModuleValueCalculatorInterface
     */
    private $moduleValueCalculator;

    /**
     * @var MockInterface|ShipTorpedoManagerInterface
     */
    private $shipTorpedoManager;

    /**
     * @var MockInterface|StuRandom
     */
    private $stuRandom;

    /**
     * @var MockInterface|ShipInterface
     */
    private ShipInterface $ship;

    private ShipAttacker $subject;

    public function setUp(): void
    {
        //injected
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->moduleValueCalculator = $this->mock(ModuleValueCalculatorInterface::class);
        $this->shipTorpedoManager = $this->mock(ShipTorpedoManagerInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new ShipAttacker(
            $this->wrapper,
            $this->moduleValueCalculator,
            $this->shipTorpedoManager,
            $this->stuRandom
        );
    }

    public function testGetPhaserVolleys(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getRump->getPhaserVolleys')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $result = $this->subject->getPhaserVolleys();

        $this->assertEquals(5, $result);
    }

    public function testGetPhaserState(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getPhaserState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->getPhaserState();

        $this->assertTrue($result);
    }

    public function testHasSufficientEnergyExpectFalseWhenNoEpsInstalled(): void
    {
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->hasSufficientEnergy(0);

        $this->assertFalse($result);
    }

    public function testHasSufficientEnergyExpectFalseWhenNotEnough(): void
    {
        $epsSystem = $this->mock(EpsSystemData::class);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);
        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $result = $this->subject->hasSufficientEnergy(1);

        $this->assertFalse($result);
    }

    public function testHasSufficientEnergyTrueWhenEnough(): void
    {
        $epsSystem = $this->mock(EpsSystemData::class);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);
        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $result = $this->subject->hasSufficientEnergy(2);

        $this->assertTrue($result);
    }

    public function testGetWeaponModuleExpectModuleWhenModuleExistent(): void
    {
        $system = $this->mock(ShipSystemInterface::class);
        $module = $this->mock(ModuleInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_PHASER)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($module);

        $result = $this->subject->getWeaponModule();

        $this->assertEquals($module, $result);
    }

    public function testGetWeaponModuleExpectErrorWhenModuleNotExistent(): void
    {
        static::expectException(RuntimeException::class);

        $system = $this->mock(ShipSystemInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_PHASER)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn(null);


        $this->subject->getWeaponModule();
    }

    public function testGetEnergyWeaponBaseDamage(): void
    {
        $rump = $this->mock(ShipRumpInterface::class);
        $system = $this->mock(ShipSystemInterface::class);
        $module = $this->mock(ModuleInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_PHASER)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($module);

        $this->ship->shouldReceive('getRump')
            ->withNoARgs()
            ->once()
            ->andReturn($rump);

        $this->moduleValueCalculator->shouldReceive('calculateModuleValue')
            ->with($rump, $module, 'getBaseDamage')
            ->once()
            ->andReturn(42);

        $result = $this->subject->getEnergyWeaponBaseDamage();

        $this->assertEquals(42, $result);
    }

    public function testReduceEpsExpectNothingWhenSystemNotExistent(): void
    {
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->reduceEps(3);
    }

    public function testReduceEpsExpectLoweringWhenSystemInstalled(): void
    {
        $epsSystem = $this->mock(EpsSystemData::class);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);
        $epsSystem->shouldReceive('lowerEps')
            ->with(3)
            ->once()
            ->andReturnSelf();
        $epsSystem->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->subject->reduceEps(3);
    }

    public function testGetUser(): void
    {
        $user = $this->mock(UserInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $result = $this->subject->getUser();

        $this->assertSame($user, $result);
    }

    public function testGetName(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('foo');

        $result = $this->subject->getName();

        $this->assertEquals('foo', $result);
    }

    public function testGetTorpedo(): void
    {
        $torpedo = $this->mock(TorpedoTypeInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedo);

        $result = $this->subject->getTorpedo();

        $this->assertEquals($torpedo, $result);
    }

    public function testGetTorpedoCount(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $result = $this->subject->getTorpedoCount();

        $this->assertEquals(5, $result);
    }

    public function testGetTorpedoState(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedoState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->getTorpedoState();

        $this->assertTrue($result);
    }

    public function testGetHitChance(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getHitChance')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $result = $this->subject->getHitChance();

        $this->assertEquals(5, $result);
    }

    public function testGetPhaserShieldDamageFactor(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getRump->getPhaserShieldDamageFactor')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $result = $this->subject->getPhaserShieldDamageFactor();

        $this->assertEquals(5, $result);
    }

    public function testGetPhaserHullDamageFactor(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getRump->getPhaserHullDamageFactor')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $result = $this->subject->getPhaserHullDamageFactor();

        $this->assertEquals(5, $result);
    }

    public function testLowerTorpedoCount(): void
    {
        $this->shipTorpedoManager->shouldReceive('changeTorpedo')
            ->with($this->wrapper, -5)
            ->once();

        $this->subject->lowerTorpedoCount(5);
    }

    public function testGetProjectileWeaponDamageExpectZeroWhenTorpedoIsNull(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->getProjectileWeaponDamage(true);

        $this->assertEquals(0, $result);
    }

    public function testGetProjectileWeaponDamageExpectZeroWhenNoTorpedoSystemInstalled(): void
    {
        $torpedo = $this->mock(TorpedoTypeInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedo);
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_TORPEDO)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->getProjectileWeaponDamage(true);

        $this->assertEquals(0, $result);
    }

    public function testGetProjectileWeaponDamageExpectCorrectValue(): void
    {
        $torpedo = $this->mock(TorpedoTypeInterface::class);
        $module = $this->mock(ModuleInterface::class);
        $system = $this->mock(ShipSystemInterface::class);
        $rump = $this->mock(ShipRumpInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedo);
        $this->ship->shouldReceive('getRump')
            ->withNoArgs()
            ->once()
            ->andReturn($rump);
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_TORPEDO)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($module);

        $torpedo->shouldReceive('getBaseDamage')
            ->withNoArgs()
            ->andReturn(1000);
        $torpedo->shouldReceive('getVariance')
            ->withNoArgs()
            ->once()
            ->andReturn(10);

        $this->moduleValueCalculator->shouldReceive('calculateModuleValue')
            ->with($rump, $module, false, 1000)
            ->once()
            ->andReturn(2000);

        $result = $this->subject->getProjectileWeaponDamage(true);

        //1900 - 2100
        $this->assertTrue($result >= 3800);
        $this->assertTrue($result <= 4200);
    }

    public function testGetTorpedoVolleys(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getRump->getTorpedoVolleys')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $result = $this->subject->getTorpedoVolleys();

        $this->assertEquals(5, $result);
    }
}
