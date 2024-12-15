<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\StuTestCase;

class EnergyPhalanxTest extends StuTestCase
{
    /**
     * @var MockInterface&ColonyInterface
     */
    private $colony;

    /**
     * @var MockInterface&ModuleRepositoryInterface
     */
    private $moduleRepository;

    private EnergyPhalanx $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->colony = $this->mock(ColonyInterface::class);
        $this->moduleRepository = $this->mock(ModuleRepositoryInterface::class);

        $this->subject = new EnergyPhalanx(
            $this->colony,
            $this->moduleRepository
        );
    }

    public function testHasSufficientEnergyExpectFalseWhenNotEnough(): void
    {
        $this->colony->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(4);

        $result = $this->subject->hasSufficientEnergy(5);

        $this->assertFalse($result);
    }

    public function testHasSufficientEnergyTrueWhenEnough(): void
    {
        $this->colony->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $result = $this->subject->hasSufficientEnergy(5);

        $this->assertTrue($result);
    }

    public function testReduceEps(): void
    {
        $this->colony->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $this->colony->shouldReceive('setEps')
            ->with(2)
            ->once();

        $this->subject->reduceEps(3);
    }

    public function testGetUser(): void
    {
        $user = $this->mock(UserInterface::class);

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $result = $this->subject->getUser();

        $this->assertSame($user, $result);
    }

    public static function provideGetNameData(): array
    {
        return [['Orbitale Disruptorphalanx', 2], ['Orbitale Disruptorphalanx', 3], ['Orbitale Phaserphalanx', 1]];
    }

    #[DataProvider('provideGetNameData')]
    public function testGetName(string $expected, int $faction): void
    {
        $this->colony->shouldReceive('getUser->getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($faction);

        $result = $this->subject->getName();

        $this->assertEquals($expected, $result);
    }

    public function testGetPhaserState(): void
    {
        $result = $this->subject->getPhaserState();

        $this->assertTrue($result);
    }

    public static function provideHitChanceData(): array
    {
        return [[2, 67], [3, 67], [1, 86]];
    }

    #[DataProvider('provideHitChanceData')]
    public function testGetHitChance(int $faction, int $expected): void
    {
        $this->colony->shouldReceive('getUser->getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($faction);

        $result = $this->subject->getHitChance();

        $this->assertEquals($expected, $result);
    }

    public static function provideGetWeaponModuleData(): array
    {
        return [[2, 3], [3, 3], [1, 1]];
    }

    #[DataProvider('provideGetWeaponModuleData')]
    public function testGetWeaponModuleExpectModuleWhenModuleExistent(int $faction, int $moduleId): void
    {
        $module = $this->mock(ModuleInterface::class);

        $this->colony->shouldReceive('getUser->getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($faction);

        $this->moduleRepository->shouldReceive('find')
            ->with($moduleId)
            ->once()
            ->andReturn($module);

        $result = $this->subject->getWeaponModule();

        $this->assertEquals($module, $result);
    }

    public function testGetWeaponModuleExpectErrorWhenModuleNotExistent(): void
    {
        static::expectException(RuntimeException::class);

        $this->colony->shouldReceive('getUser->getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->moduleRepository->shouldReceive('find')
            ->with(3)
            ->once()
            ->andReturn(null);

        $this->subject->getWeaponModule();
    }

    public static function provideGetEnergyWeaponBaseDamageData(): array
    {
        return [[180, 2], [180, 3], [250, 1]];
    }

    #[DataProvider('provideGetEnergyWeaponBaseDamageData')]
    public function testGetEnergyWeaponBaseDamage(int $expected, int $faction): void
    {
        $this->colony->shouldReceive('getUser->getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($faction);

        $result = $this->subject->getEnergyWeaponBaseDamage();

        $this->assertEquals($expected, $result);
    }

    public static function provideGetPhaserVolleysData(): array
    {
        return [[5, 2], [5, 3], [3, 1]];
    }

    #[DataProvider('provideGetPhaserVolleysData')]
    public function testGetPhaserVolleys(int $expected, int $faction): void
    {
        $this->colony->shouldReceive('getUser->getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($faction);

        $result = $this->subject->getPhaserVolleys();

        $this->assertEquals($expected, $result);
    }

    public function testGetPhaserShieldDamageFactor(): void
    {
        $result = $this->subject->getPhaserShieldDamageFactor();

        $this->assertEquals(200, $result);
    }

    public function testGetPhaserHullDamageFactor(): void
    {
        $result = $this->subject->getPhaserHullDamageFactor();

        $this->assertEquals(100, $result);
    }
}
