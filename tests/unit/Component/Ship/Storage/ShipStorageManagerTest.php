<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Storage;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Ship\Storage\Exception\CommodityMissingException;
use Stu\Component\Ship\Storage\Exception\QuantityTooSmallException;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\StuTestCase;

class ShipStorageManagerTest extends StuTestCase
{
    /**
     * @var StorageRepositoryInterface|MockInterface|null
     */
    private $storageRepository;

    private ?ShipStorageManager $manager;

    #[Override]
    public function setUp(): void
    {
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);

        $this->manager = new ShipStorageManager(
            $this->storageRepository,
        );
    }

    public function testLowerStorageThrowExceptionOnMissingCommodity(): void
    {
        $this->expectException(CommodityMissingException::class);

        $ship = $this->mock(ShipInterface::class);
        $commodity = $this->mock(CommodityInterface::class);

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $ship->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $this->manager->lowerStorage($ship, $commodity, 666);
    }

    public function testLowerStorageThrowExceptionIfQuantitityIsTooSmall(): void
    {
        $this->expectException(QuantityTooSmallException::class);

        $ship = $this->mock(ShipInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(StorageInterface::class);

        $amount = 666;
        $storedAmount = 33;
        $commodityId = 42;

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);

        $ship->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([
                $commodityId => $storageItem,
            ]));

        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($storedAmount);

        $this->manager->lowerStorage($ship, $commodity, $amount);
    }

    public function testLowerStorageRemovesCommodityFromStorageIfQuantityIsSame(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(StorageInterface::class);

        $amount = 666;
        $commodityId = 42;

        $storage = new ArrayCollection([
            $commodityId => $storageItem,
        ]);

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);

        $ship->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);

        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($amount);

        $this->storageRepository->shouldReceive('delete')
            ->with($storageItem)
            ->once();

        $this->manager->lowerStorage($ship, $commodity, $amount);

        $this->assertFalse(
            $storage->offsetExists($commodityId)
        );
    }

    public function testLowerStorageUpdatesStorageItem(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(StorageInterface::class);

        $amount = 666;
        $storedAmount = 777;
        $commodityId = 42;

        $storage = new ArrayCollection([
            $commodityId => $storageItem,
        ]);

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);

        $ship->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);

        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($storedAmount);
        $storageItem->shouldReceive('setAmount')
            ->with($storedAmount - $amount)
            ->once();

        $this->storageRepository->shouldReceive('save')
            ->with($storageItem)
            ->once();

        $this->manager->lowerStorage($ship, $commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($commodityId)
        );
    }

    public function testUpperStorageCreatesNewStorageItem(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(StorageInterface::class);
        $storage = new ArrayCollection();
        $user = $this->mock(UserInterface::class);

        $amount = 666;
        $commodityId = 42;
        $storedAmount = 0;

        $ship->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);

        $this->storageRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($storageItem);
        $this->storageRepository->shouldReceive('save')
            ->with($storageItem)
            ->once();

        $storageItem->shouldReceive('setUser')
            ->with($user)
            ->once()
            ->andReturnSelf();
        $storageItem->shouldReceive('setShip')
            ->with($ship)
            ->once()
            ->andReturnSelf();
        $storageItem->shouldReceive('setCommodity')
            ->with($commodity)
            ->once()
            ->andReturnSelf();
        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($storedAmount);
        $storageItem->shouldReceive('setAmount')
            ->with($amount)
            ->once();

        $this->manager->upperStorage($ship, $commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($commodityId)
        );
    }

    public function testUpperStorageUpdateExistingStorageItem(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(StorageInterface::class);

        $amount = 666;
        $commodityId = 42;
        $storedAmount = 0;
        $storage = new ArrayCollection([
            $commodityId => $storageItem,
        ]);

        $ship->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);

        $this->storageRepository->shouldReceive('save')
            ->with($storageItem)
            ->once();

        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($storedAmount);
        $storageItem->shouldReceive('setAmount')
            ->with($amount)
            ->once();

        $this->manager->upperStorage($ship, $commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($commodityId)
        );
    }
}
