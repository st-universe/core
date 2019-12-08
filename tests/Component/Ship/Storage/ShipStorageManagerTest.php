<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Storage;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Ship\Storage\Exception\CommodityMissingException;
use Stu\Component\Ship\Storage\Exception\QuantityTooSmallException;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipStorageInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\ShipStorageRepositoryInterface;
use Stu\StuTestCase;

class ShipStorageManagerTest extends StuTestCase
{
    /**
     * @var ShipStorageRepositoryInterface|MockInterface|null
     */
    private ?MockInterface $shipStorageRepository;

    /**
     * @var ShipStorageManager
     */
    private ?ShipStorageManager $manager;

    public function setUp(): void
    {
        $this->shipStorageRepository = $this->mock(ShipStorageRepositoryInterface::class);

        $this->manager = new ShipStorageManager(
            $this->shipStorageRepository,
        );
    }

    public function testLowerStorageThrowExceptionOnMissingCommodity(): void {
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

    public function testLowerStorageThrowExceptionIfQuantitityIsTooSmall(): void {
        $this->expectException(QuantityTooSmallException::class);

        $ship = $this->mock(ShipInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(ShipStorageInterface::class);

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
                $commodityId => $storageItem
            ]));

        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($storedAmount);

        $this->manager->lowerStorage($ship, $commodity, $amount);
    }

    public function testLowerStorageRemovesCommodityFromStorageIfQuantityIsSame(): void {
        $ship = $this->mock(ShipInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(ShipStorageInterface::class);

        $amount = 666;
        $commodityId = 42;

        $storage = new ArrayCollection([
            $commodityId => $storageItem
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

        $this->shipStorageRepository->shouldReceive('delete')
            ->with($storageItem)
            ->once();

        $this->manager->lowerStorage($ship, $commodity, $amount);

        $this->assertFalse(
            $storage->offsetExists($commodityId)
        );
    }

    public function testLowerStorageUpdatesStorageItem(): void {
        $ship = $this->mock(ShipInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(ShipStorageInterface::class);

        $amount = 666;
        $storedAmount = 777;
        $commodityId = 42;

        $storage = new ArrayCollection([
            $commodityId => $storageItem
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

        $this->shipStorageRepository->shouldReceive('save')
            ->with($storageItem)
            ->once();

        $this->manager->lowerStorage($ship, $commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($commodityId)
        );
    }

    public function testUpperStorageCreatesNewStorageItem(): void {
        $ship = $this->mock(ShipInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(ShipStorageInterface::class);
        $storage = new ArrayCollection();

        $amount = 666;
        $commodityId = 42;
        $storedAmount = 0;

        $ship->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);

        $this->shipStorageRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($storageItem);
        $this->shipStorageRepository->shouldReceive('save')
            ->with($storageItem)
            ->once();

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
            ->with($amount + $storedAmount)
            ->once();

        $this->manager->upperStorage($ship, $commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($commodityId)
        );
    }

    public function testUpperStorageUpdateExistingStorageItem(): void {
        $ship = $this->mock(ShipInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(ShipStorageInterface::class);

        $amount = 666;
        $commodityId = 42;
        $storedAmount = 0;
        $storage = new ArrayCollection([
            $commodityId => $storageItem
        ]);

        $ship->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);

        $this->shipStorageRepository->shouldReceive('save')
            ->with($storageItem)
            ->once();

        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($storedAmount);
        $storageItem->shouldReceive('setAmount')
            ->with($amount + $storedAmount)
            ->once();

        $this->manager->upperStorage($ship, $commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($commodityId)
        );
    }
}
