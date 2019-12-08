<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Colony\Storage\ColonyStorageManager;
use Stu\Component\Colony\Storage\Exception\CommodityMissingException;
use Stu\Component\Colony\Storage\Exception\QuantityTooSmallException;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyStorageInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\StuTestCase;

class ColonyStorageManagerTest extends StuTestCase
{
    /**
     * @var ColonyStorageRepositoryInterface|MockInterface|null
     */
    private ?MockInterface $colonyStorageRepository;

    /**
     * @var CommodityRepositoryInterface|MockInterface|null
     */
    private ?MockInterface $commodityRepository;

    /**
     * @var ColonyStorageManager
     */
    private ?ColonyStorageManager $manager;

    public function setUp(): void
    {
        $this->colonyStorageRepository = $this->mock(ColonyStorageRepositoryInterface::class);
        $this->commodityRepository = $this->mock(CommodityRepositoryInterface::class);

        $this->manager = new ColonyStorageManager(
            $this->colonyStorageRepository,
            $this->commodityRepository
        );
    }

    public function testLowerStorageThrowExceptionOnMissingCommodity(): void {
        $this->expectException(CommodityMissingException::class);

        $colony = $this->mock(ColonyInterface::class);
        $commodity = $this->mock(CommodityInterface::class);

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $this->manager->lowerStorage($colony, $commodity, 666);
    }

    public function testLowerStorageThrowExceptionIfQuantitityIsTooSmall(): void {
        $this->expectException(QuantityTooSmallException::class);

        $colony = $this->mock(ColonyInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(ColonyStorageInterface::class);

        $amount = 666;
        $storedAmount = 33;
        $commodityId = 42;

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([
                $commodityId => $storageItem
            ]));

        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($storedAmount);

        $this->manager->lowerStorage($colony, $commodity, $amount);
    }

    public function testLowerStorageRemovesCommodityFromStorageIfQuantityIsSame(): void {
        $colony = $this->mock(ColonyInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(ColonyStorageInterface::class);

        $amount = 666;
        $commodityId = 42;

        $storage = new ArrayCollection([
            $commodityId => $storageItem
        ]);

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $colony->shouldReceive('clearCache')
            ->withNoArgs()
            ->once();

        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($amount);

        $this->colonyStorageRepository->shouldReceive('delete')
            ->with($storageItem)
            ->once();

        $this->manager->lowerStorage($colony, $commodity, $amount);

        $this->assertFalse(
            $storage->offsetExists($commodityId)
        );
    }

    public function testLowerStorageUpdatesStorageItem(): void {
        $colony = $this->mock(ColonyInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(ColonyStorageInterface::class);

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

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $colony->shouldReceive('clearCache')
            ->withNoArgs()
            ->once();

        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($storedAmount);
        $storageItem->shouldReceive('setAmount')
            ->with($storedAmount - $amount)
            ->once();

        $this->colonyStorageRepository->shouldReceive('save')
            ->with($storageItem)
            ->once();

        $this->manager->lowerStorage($colony, $commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($commodityId)
        );
    }

    public function testUpperStorageCreatesNewStorageItem(): void {
        $colony = $this->mock(ColonyInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(ColonyStorageInterface::class);
        $storage = new ArrayCollection();

        $amount = 666;
        $commodityId = 42;
        $storedAmount = 0;

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $colony->shouldReceive('clearCache')
            ->withNoArgs()
            ->once();

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);

        $this->colonyStorageRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($storageItem);
        $this->colonyStorageRepository->shouldReceive('save')
            ->with($storageItem)
            ->once();

        $storageItem->shouldReceive('setColony')
            ->with($colony)
            ->once()
            ->andReturnSelf();
        $storageItem->shouldReceive('setGood')
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

        $this->manager->upperStorage($colony, $commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($commodityId)
        );
    }

    public function testUpperStorageUpdateExistingStorageItem(): void {
        $colony = $this->mock(ColonyInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $storageItem = $this->mock(ColonyStorageInterface::class);

        $amount = 666;
        $commodityId = 42;
        $storedAmount = 0;
        $storage = new ArrayCollection([
            $commodityId => $storageItem
        ]);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $colony->shouldReceive('clearCache')
            ->withNoArgs()
            ->once();

        $commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);

        $this->colonyStorageRepository->shouldReceive('save')
            ->with($storageItem)
            ->once();

        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($storedAmount);
        $storageItem->shouldReceive('setAmount')
            ->with($amount + $storedAmount)
            ->once();

        $this->manager->upperStorage($colony, $commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($commodityId)
        );
    }
}
