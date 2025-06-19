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
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\StuTestCase;

class StorageManagerTest extends StuTestCase
{
    private StorageRepositoryInterface&MockInterface $storageRepository;
    private ColonyInterface&MockInterface $colony;
    private CommodityInterface&MockInterface $commodity;

    public $commodityId;

    private StorageManagerInterface $manager;

    #[Override]
    public function setUp(): void
    {
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);
        $this->colony = $this->mock(ColonyInterface::class);
        $this->commodity = $this->mock(CommodityInterface::class);

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

    public static function getTransferEntitiesProvider(): array
    {
        return [
            [Mockery::mock(ColonyInterface::class), TransferEntityTypeEnum::COLONY, 'setColony'],
            [Mockery::mock(SpacecraftInterface::class), TransferEntityTypeEnum::SHIP, 'setSpacecraft'],
            [Mockery::mock(SpacecraftInterface::class), TransferEntityTypeEnum::STATION, 'setSpacecraft']
        ];
    }

    #[DataProvider('getTransferEntitiesProvider')]
    public function testUpperStorageCreatesNewStorageItem(
        EntityWithStorageInterface&MockInterface $entity,
        TransferEntityTypeEnum $entityType,
        string $expectedSetter
    ): void {
        $storageItem = $this->mock(StorageInterface::class);
        $user = $this->mock(UserInterface::class);
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
        $storageItem->shouldReceive($expectedSetter)
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
        $storageItem = $this->mock(StorageInterface::class);

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
