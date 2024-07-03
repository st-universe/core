<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Storage;

use Override;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Colony\Storage\Exception\CommodityMissingException;
use Stu\Component\Colony\Storage\Exception\QuantityTooSmallException;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\StuTestCase;

class ColonyStorageManagerTest extends StuTestCase
{
    /**
     * @var StorageRepositoryInterface|MockInterface|null
     */
    private ?MockInterface $storageRepository;

    /**
     * @var ColonyStorageManager|null
     */
    private ?ColonyStorageManager $manager;

    private ColonyInterface $colony;

    private CommodityInterface $commodity;

    private LoggerUtilInterface $loggerUtil;

    public $commodityId;

    #[Override]
    public function setUp(): void
    {
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);
        $this->colony = $this->mock(ColonyInterface::class);
        $this->commodity = $this->mock(CommodityInterface::class);

        $loggerUtilFactory = $this->mock(LoggerUtilFactoryInterface::class);
        $this->loggerUtil = $this->mock(LoggerUtilInterface::class);

        $loggerUtilFactory->shouldReceive('getLoggerUtil')
            ->withNoArgs()
            ->once()
            ->andReturn($this->loggerUtil);

        $this->manager = new ColonyStorageManager(
            $this->storageRepository,
            $loggerUtilFactory
        );
    }

    public function testLowerStorageThrowExceptionOnMissingCommodity(): void
    {
        $this->expectException(CommodityMissingException::class);

        $this->commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $this->manager->lowerStorage($this->colony, $this->commodity, 666);
    }

    public function testLowerStorageThrowExceptionIfQuantitityIsTooSmall(): void
    {
        $this->expectException(QuantityTooSmallException::class);

        $storageItem = $this->mock(StorageInterface::class);

        $amount = 666;
        $storedAmount = 33;
        $this->commodityId = 42;

        $this->commodity->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($this->commodityId);

        $this->commodity->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('Latinum');

        $this->colony->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([
                $this->commodityId => $storageItem,
            ]));

        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($storedAmount);

        $this->manager->lowerStorage($this->colony, $this->commodity, $amount);
    }

    public function testLowerStorageRemovesCommodityFromStorageIfQuantityIsSame(): void
    {
        $storageItem = $this->mock(StorageInterface::class);

        $amount = 666;
        $this->commodityId = 42;

        $storage = new ArrayCollection([
            $this->commodityId => $storageItem,
        ]);

        $this->commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->commodityId);

        $this->colony->shouldReceive('getStorage')
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

        $this->manager->lowerStorage($this->colony, $this->commodity, $amount);

        $this->assertFalse(
            $storage->offsetExists($this->commodityId)
        );
    }

    public function testLowerStorageUpdatesStorageItem(): void
    {
        $storageItem = $this->mock(StorageInterface::class);

        $amount = 666;
        $storedAmount = 777;
        $this->commodityId = 42;

        $storage = new ArrayCollection([
            $this->commodityId => $storageItem,
        ]);

        $this->commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->commodityId);

        $this->colony->shouldReceive('getStorage')
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

        $this->manager->lowerStorage($this->colony, $this->commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($this->commodityId)
        );
    }

    public function testUpperStorageCreatesNewStorageItem(): void
    {
        $storageItem = $this->mock(StorageInterface::class);
        $user = $this->mock(UserInterface::class);
        $storage = new ArrayCollection();

        $amount = 666;
        $this->commodityId = 42;
        $storedAmount = 0;

        $this->loggerUtil->shouldReceive('doLog')
            ->withNoArgs()
            ->times(6)
            ->andReturnFalse();

        $this->colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->commodityId);

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
        $storageItem->shouldReceive('setColony')
            ->with($this->colony)
            ->once()
            ->andReturnSelf();
        $storageItem->shouldReceive('setCommodity')
            ->with($this->commodity)
            ->once()
            ->andReturnSelf();
        $storageItem->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn($storedAmount);
        $storageItem->shouldReceive('setAmount')
            ->with($amount)
            ->once();

        $this->manager->upperStorage($this->colony, $this->commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($this->commodityId)
        );
    }

    public function testUpperStorageUpdateExistingStorageItem(): void
    {
        $storageItem = $this->mock(StorageInterface::class);

        $amount = 666;
        $this->commodityId = 42;
        $storedAmount = 0;
        $storage = new ArrayCollection([
            $this->commodityId => $storageItem,
        ]);

        $this->loggerUtil->shouldReceive('doLog')
            ->withNoArgs()
            ->times(6)
            ->andReturnFalse();

        $this->colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);

        $this->commodity->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->commodityId);

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

        $this->manager->upperStorage($this->colony, $this->commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($this->commodityId)
        );
    }
}
