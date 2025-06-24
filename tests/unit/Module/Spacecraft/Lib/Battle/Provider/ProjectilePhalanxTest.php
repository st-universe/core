<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\StuTestCase;

class ProjectilePhalanxTest extends StuTestCase
{
    private MockInterface&ColonyInterface $colony;
    private MockInterface&StorageManagerInterface $storageManager;

    private ProjectilePhalanx $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->colony = $this->mock(ColonyInterface::class);
        $this->storageManager = $this->mock(StorageManagerInterface::class);

        $this->subject = new ProjectilePhalanx(
            $this->colony,
            $this->storageManager
        );
    }

    public function testHasSufficientEnergyExpectFalseWhenNotEnough(): void
    {
        $this->colony->shouldReceive('getChangeable->getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(4);

        $result = $this->subject->hasSufficientEnergy(5);

        $this->assertFalse($result);
    }

    public function testHasSufficientEnergyTrueWhenEnough(): void
    {
        $this->colony->shouldReceive('getChangeable->getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $result = $this->subject->hasSufficientEnergy(5);

        $this->assertTrue($result);
    }

    public function testReduceEps(): void
    {
        $this->colony->shouldReceive('getChangeable->lowerEps')
            ->with(3)
            ->once();

        $this->subject->reduceEps(3);
    }

    public function testGetUser(): void
    {
        $this->colony->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->subject->getUserId();

        $this->assertEquals(42, $result);
    }

    public function testGetName(): void
    {
        $result = $this->subject->getName();

        $this->assertEquals('Orbitale Torpedophalanx', $result);
    }

    public function testGetTorpedoCountExpectZeroWhenTorpedoIsNull(): void
    {
        $this->colony->shouldReceive('getChangeable->getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->getTorpedoCount();

        $this->assertEquals(0, $result);
    }

    public function testGetTorpedoCountExpectZeroWhenNoTorpedosInStorage(): void
    {
        $torpedo = $this->mock(TorpedoTypeInterface::class);

        $this->colony->shouldReceive('getChangeable->getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedo);
        $this->colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $torpedo->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->subject->getTorpedoCount();

        $this->assertEquals(0, $result);
    }

    public static function provideGetTorpedoStateData(): array
    {
        return [[false, 0], [true, 1]];
    }

    #[DataProvider('provideGetTorpedoStateData')]
    public function testGetTorpedoState(bool $expected, int $count): void
    {
        $torpedo = $this->mock(TorpedoTypeInterface::class);
        $storage = $this->mock(StorageInterface::class);

        $this->colony->shouldReceive('getChangeable->getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedo);
        $this->colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([42 => $storage]));

        $torpedo->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($count);

        $result = $this->subject->getTorpedoState();

        $this->assertEquals($expected, $result);
    }

    public function testGetTorpedoCountExpectStorageAmountWhenTorpedosInStorage(): void
    {
        $torpedo = $this->mock(TorpedoTypeInterface::class);
        $storage = $this->mock(StorageInterface::class);

        $this->colony->shouldReceive('getChangeable->getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedo);
        $this->colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([42 => $storage]));

        $torpedo->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $result = $this->subject->getTorpedoCount();

        $this->assertEquals(5, $result);
    }

    public function testGetHitChance(): void
    {
        $result = $this->subject->getHitChance();

        $this->assertEquals(75, $result);
    }

    public function testLowerTorpedoCountExpectNothingWhenTorpedoIsNull(): void
    {
        $this->colony->shouldReceive('getChangeable->getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->lowerTorpedoCount(5);
    }

    public function testLowerTorpedoCountExpectLowering(): void
    {
        $torpedo = $this->mock(TorpedoTypeInterface::class);
        $commodity = $this->mock(CommodityInterface::class);

        $this->colony->shouldReceive('getChangeable->getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedo);

        $torpedo->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);

        $this->storageManager->shouldReceive('lowerStorage')
            ->with($this->colony, $commodity, 5)
            ->once()
            ->andReturn($commodity);

        $this->subject->lowerTorpedoCount(5);
    }

    public function testGetProjectileWeaponDamageExpectZeroWhenTorpedoIsNull(): void
    {
        $this->colony->shouldReceive('getChangeable->getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->getProjectileWeaponDamage(true);

        $this->assertEquals(0, $result);
    }

    public function testGetProjectileWeaponDamageExpectCorrectValue(): void
    {
        $torpedo = $this->mock(TorpedoTypeInterface::class);

        $this->colony->shouldReceive('getChangeable->getTorpedo')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedo);

        $torpedo->shouldReceive('getBaseDamage')
            ->withNoArgs()
            ->once()
            ->andReturn(1000);
        $torpedo->shouldReceive('getVariance')
            ->withNoArgs()
            ->once()
            ->andReturn(10);

        $result = $this->subject->getProjectileWeaponDamage(true);

        $this->assertTrue($result >= 1800);
        $this->assertTrue($result <= 2200);
    }

    public function testGetTorpedoVolleys(): void
    {
        $result = $this->subject->getTorpedoVolleys();

        $this->assertEquals(7, $result);
    }
}
