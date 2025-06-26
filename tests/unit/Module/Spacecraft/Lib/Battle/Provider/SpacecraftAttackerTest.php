<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Component\Spacecraft\System\Data\EnergyWeaponSystemData;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\TorpedoType;
use Stu\StuTestCase;

class SpacecraftAttackerTest extends StuTestCase
{
    private MockInterface&ShipWrapperInterface $wrapper;

    private MockInterface&ShipTorpedoManagerInterface $shipTorpedoManager;

    private MockInterface&StuRandom $stuRandom;

    private MockInterface&Ship $ship;

    private SpacecraftAttacker $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->shipTorpedoManager = $this->mock(ShipTorpedoManagerInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->ship = $this->mock(Ship::class);

        $this->subject = new SpacecraftAttacker(
            $this->wrapper,
            $this->shipTorpedoManager,
            false,
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
        $system = $this->mock(SpacecraftSystem::class);
        $module = $this->mock(Module::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::PHASER)
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

        $system = $this->mock(SpacecraftSystem::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::PHASER)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn(null);


        $this->subject->getWeaponModule();
    }

    public function testGetEnergyWeaponBaseDamageExpectZeroIfNoEnergyWeaponInstalled(): void
    {
        $this->wrapper->shouldReceive('getEnergyWeaponSystemData')
            ->withNoArgs()
            ->andReturn(null);

        $result = $this->subject->getEnergyWeaponBaseDamage();

        $this->assertEquals(0, $result);
    }

    public function testGetEnergyWeaponBaseDamageExpectWeaponValueIfInstalled(): void
    {
        $energyWeapon = $this->mock(EnergyWeaponSystemData::class);

        $this->wrapper->shouldReceive('getEnergyWeaponSystemData')
            ->withNoArgs()
            ->andReturn($energyWeapon);

        $energyWeapon->shouldReceive('getBaseDamage')
            ->withNoArgs()
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
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->subject->getUserId();

        $this->assertEquals(42, $result);
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
        $torpedo = $this->mock(TorpedoType::class);

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
        $this->wrapper->shouldReceive('getComputerSystemDataMandatory->getHitChance')
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
        $torpedo = $this->mock(TorpedoType::class);
        $system = $this->mock(SpacecraftSystem::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedo);
        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TORPEDO)
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
        $torpedo = $this->mock(TorpedoType::class);
        $module = $this->mock(Module::class);
        $system = $this->mock(SpacecraftSystem::class);
        $this->mock(SpacecraftRump::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedo);
        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TORPEDO)
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

        $result = $this->subject->getProjectileWeaponDamage(true);

        //1800 - 2200
        $this->assertTrue($result >= 1800);
        $this->assertTrue($result <= 2200);
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
