<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Storage;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Lib\Transfer\Storage\Exception\CommodityMissingException;
use Stu\Lib\Transfer\Storage\Exception\QuantityTooSmallException;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\StuTestCase;

class StorageManagerTest extends StuTestCase
{
    private StorageRepositoryInterface&MockInterface $storageRepository;
    private Colony&MockInterface $colony;
    private Commodity&MockInterface $commodity;

    public $commodityId;

    private StorageManagerInterface $manager;

    #[Override]
    public function setUp(): void
    {
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);
        $this->colony = $this->mock(Colony::class);
        $this->commodity = $this->mock(Commodity::class);

        $this->manager = new StorageManager(
            $this->storageRepository
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

        $storageItem = $this->mock(Storage::class);

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
        $this->colony->shouldReceive('getTransferEntityType')
            ->withNoArgs()
            ->once()
            ->andReturn(TransferEntityTypeEnum::COLONY);
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
        $storageItem = $this->mock(Storage::class);

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
        $storageItem = $this->mock(Storage::class);

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

    public static function getTransferEntitiesProvider(): array
    {
        return [
            [Mockery::mock(Colony::class), TransferEntityTypeEnum::COLONY],
            [Mockery::mock(Spacecraft::class), TransferEntityTypeEnum::SHIP],
            [Mockery::mock(Spacecraft::class), TransferEntityTypeEnum::STATION]
        ];
    }

    #[DataProvider('getTransferEntitiesProvider')]
    public function testUpperStorageCreatesNewStorageItem(
        EntityWithStorageInterface&MockInterface $entity,
        TransferEntityTypeEnum $entityType
    ): void {
        $storageItem = $this->mock(Storage::class);
        $user = $this->mock(User::class);
        $storage = new ArrayCollection();

        $amount = 666;
        $this->commodityId = 42;
        $storedAmount = 0;

        $entity->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $entity->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $entity->shouldReceive('getTransferEntityType')
            ->withNoArgs()
            ->once()
            ->andReturn($entityType);

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
        $storageItem->shouldReceive('setEntity')
            ->with($entity)
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

        $this->manager->upperStorage($entity, $this->commodity, $amount);

        $this->assertTrue(
            $storage->offsetExists($this->commodityId)
        );
    }

    public function testUpperStorageUpdateExistingStorageItem(): void
    {
        $storageItem = $this->mock(Storage::class);

        $amount = 666;
        $this->commodityId = 42;
        $storedAmount = 0;
        $storage = new ArrayCollection([
            $this->commodityId => $storageItem,
        ]);

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
