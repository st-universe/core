<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Torpedo;

use InvalidArgumentException;
use Mockery\MockInterface;
use Override;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TorpedoStorage;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;
use Stu\StuTestCase;

class ShipTorpedoManagerTest extends StuTestCase
{
    private MockInterface&ClearTorpedoInterface $clearTorpedo;
    private MockInterface&TorpedoStorageRepositoryInterface $torpedoStorageRepository;
    private MockInterface&StorageRepositoryInterface $storageRepository;

    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&Ship $ship;
    private MockInterface&TorpedoType $torpedoType;

    private ShipTorpedoManagerInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->clearTorpedo = $this->mock(ClearTorpedoInterface::class);
        $this->torpedoStorageRepository = $this->mock(TorpedoStorageRepositoryInterface::class);
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);

        //params
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(Ship::class);
        $this->torpedoType = $this->mock(TorpedoType::class);

        $this->subject = new ShipTorpedoManager(
            $this->clearTorpedo,
            $this->torpedoStorageRepository,
            $this->storageRepository
        );
    }

    public function testChangeTorpedoExpectErrorWhenStorageEmptyAndNoTypeSpecified(): void
    {
        static::expectException(InvalidArgumentException::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedoStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->changeTorpedo($this->wrapper, 42, null);
    }

    public function testChangeTorpedoExpectCreationOfNewStorageWhenShipIsEmptyAndTypeIsSpecified(): void
    {
        $torpedoStorage = $this->mock(TorpedoStorage::class);
        $storage = $this->mock(Storage::class);
        $user = $this->mock(User::class);
        $commodity = $this->mock(Commodity::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedoStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->ship->shouldReceive('setTorpedoStorage')
            ->with($torpedoStorage)
            ->once();

        $this->torpedoType->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);

        $this->torpedoStorageRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedoStorage);
        $this->torpedoStorageRepository->shouldReceive('save')
            ->with($torpedoStorage)
            ->once();
        $torpedoStorage->shouldReceive('setSpacecraft')
            ->with($this->ship)
            ->once();
        $torpedoStorage->shouldReceive('setTorpedo')
            ->with($this->torpedoType)
            ->once();
        $torpedoStorage->shouldReceive('setStorage')
            ->with($storage)
            ->once();

        $this->storageRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $this->storageRepository->shouldReceive('save')
            ->with($storage)
            ->once();
        $storage->shouldReceive('setUser')
            ->with($user)
            ->once();
        $storage->shouldReceive('setCommodity')
            ->with($commodity)
            ->once();
        $storage->shouldReceive('setAmount')
            ->with(42)
            ->once();
        $storage->shouldReceive('setTorpedoStorage')
            ->with($torpedoStorage)
            ->once();

        $this->subject->changeTorpedo($this->wrapper, 42, $this->torpedoType);
    }

    public function testChangeTorpedoExpectTorpedoClearanceWhenSetToZero(): void
    {
        $torpedoStorage = $this->mock(TorpedoStorage::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedoStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedoStorage);

        $torpedoStorage->shouldReceive('getStorage->getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->clearTorpedo->shouldReceive('clearTorpedoStorage')
            ->with($this->wrapper)
            ->once();

        $this->subject->changeTorpedo($this->wrapper, -42, $this->torpedoType);
    }

    public function testChangeTorpedoExpectChangingOfAmount(): void
    {
        $torpedoStorage = $this->mock(TorpedoStorage::class);
        $storage = $this->mock(Storage::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedoStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($torpedoStorage);

        $storage->shouldReceive('setAmount')
            ->with(47)
            ->once();
        $storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn(42);
        $this->storageRepository->shouldReceive('save')
            ->with($storage)
            ->once();

        $torpedoStorage->shouldReceive('getStorage')
            ->withNoArgs()
            ->andReturn($storage);

        $this->subject->changeTorpedo($this->wrapper, 5, $this->torpedoType);
    }
}
