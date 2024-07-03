<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Mockery\MockInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\StuTestCase;

class TradePostStorageManagerTest extends StuTestCase
{
    /**
     * @var StorageRepositoryInterface|MockInterface|null
     */
    private ?MockInterface $storageRepository;

    /**
     * @var CommodityRepositoryInterface|MockInterface|null
     */
    private ?MockInterface $commodityRepository;

    private ?TradePostStorageManagerInterface $manager;

    private TradePostInterface $tradePost;

    private StorageInterface $storage;

    private UserInterface $user;

    public function setUp(): void
    {
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);
        $this->commodityRepository = $this->mock(CommodityRepositoryInterface::class);
        $this->tradePost = $this->mock(TradePostInterface::class);
        $this->storage = $this->mock(StorageInterface::class);
        $this->user = $this->mock(UserInterface::class);

        $this->manager = new TradePostStorageManager(
            $this->storageRepository,
            $this->commodityRepository,
            $this->tradePost,
            $this->user
        );
    }

    public function testGetStorageSum(): void
    {
        $this->mockStorage();

        $this->storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(13);
        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->manager->getStorageSum();

        $this->assertEquals(13, $result);
    }

    public function testGetFreeStorage(): void
    {
        $this->mockStorage();

        $this->tradePost->shouldReceive('getStorage')
            ->withNoArgs()->once()->andReturn(20);

        $this->storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(13);
        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->manager->getFreeStorage();

        $this->assertEquals(7, $result);
    }

    public function testGetStorage(): void
    {
        $this->mockStorage();

        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->manager->getStorage();
        $result = $this->manager->getStorage();

        $this->assertEquals(1, count($result));
        $this->assertEquals($this->storage, $result[1]);
    }

    private function mockStorage(): void
    {
        $this->tradePost->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(55);

        $this->storageRepository->shouldReceive('getByTradePostAndUser')
            ->with(55, 42)
            ->once()
            ->andReturn([$this->storage]);

        $this->storage->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
    }

    public function testUpperStorageCreateNewIfNonExistent(): void
    {
        $this->mockStorage();
        $newStorage = $this->mock(StorageInterface::class);
        $commodity = $this->mock(CommodityInterface::class);

        $this->storageRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($newStorage);
        $this->storageRepository->shouldReceive('save')
            ->with($newStorage)
            ->once()
            ->andReturn($newStorage);
        $this->commodityRepository->shouldReceive('find')
            ->with(2)
            ->once()
            ->andReturn($commodity);
        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $newStorage->shouldReceive('setUser')
            ->with($this->user)
            ->once();
        $newStorage->shouldReceive('setCommodity')
            ->with($commodity)
            ->once();
        $newStorage->shouldReceive('setTradePost')
            ->with($this->tradePost)
            ->once();
        $newStorage->shouldReceive('setAmount')
            ->with(666)
            ->once();
        $newStorage->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->manager->upperStorage(2, 666);

        $result = $this->manager->getStorage();

        $this->assertNotNull($result[2]);
        $this->assertEquals($newStorage, $result[2]);
    }

    public function testUpperStorageModifyIfExistent(): void
    {
        $this->mockStorage();

        $this->storageRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->never();
        $this->storageRepository->shouldReceive('save')
            ->with($this->storage)
            ->once();

        $this->storage->shouldReceive('setAmount')
            ->with(17)
            ->once();
        $this->storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(7);
        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->upperStorage(1, 10);

        $result = $this->manager->getStorage();

        $this->assertEquals(1, $result->count());
    }

    public function testLowerStorageReduceIfEnoughExistent(): void
    {
        $this->mockStorage();

        $this->storageRepository->shouldReceive('save')
            ->with($this->storage)
            ->once();

        $this->storage->shouldReceive('setAmount')
            ->with(12)
            ->once();
        $this->storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->twice()
            ->andReturn(100);
        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->lowerStorage(1, 88);
    }

    public function testLowerStorageRemoveIfEmpty(): void
    {
        $this->mockStorage();

        $this->storageRepository->shouldReceive('delete')
            ->with($this->storage)
            ->once();

        $this->storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->lowerStorage(1, 100);

        $this->assertEquals(0, $this->manager->getStorage()->count());
    }
}
