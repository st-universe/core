<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Torpedo;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TorpedoStorage;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;
use Stu\StuTestCase;

class ClearTorpedoTest extends StuTestCase
{
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;

    private MockInterface&TorpedoStorageRepositoryInterface $torpedoStorageRepository;

    private MockInterface&StorageRepositoryInterface $storageRepository;

    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&Ship $ship;

    private ClearTorpedoInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        //injected
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->torpedoStorageRepository = $this->mock(TorpedoStorageRepositoryInterface::class);
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);

        //params
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(Ship::class);

        $this->subject = new ClearTorpedo(
            $this->spacecraftSystemManager,
            $this->torpedoStorageRepository,
            $this->storageRepository
        );
    }

    public function testClearTorpedoStorageExpectNothingWhenStorageEmpty(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getTorpedoStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->clearTorpedoStorage($this->wrapper);
    }

    public function testClearTorpedoStorageExpectClearanceWhenStorageFilled(): void
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
        $this->ship->shouldReceive('setTorpedoStorage')
            ->with(null)
            ->once();
        $this->ship->shouldReceive('getTorpedoState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $torpedoStorage->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);

        $this->storageRepository->shouldReceive('delete')
            ->with($storage)
            ->once();
        $this->torpedoStorageRepository->shouldReceive('delete')
            ->with($torpedoStorage)
            ->once();

        $this->subject->clearTorpedoStorage($this->wrapper);
    }

    public function testClearTorpedoStorageExpectClearanceAndDeactivationWhenActive(): void
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
        $this->ship->shouldReceive('setTorpedoStorage')
            ->with(null)
            ->once();
        $this->ship->shouldReceive('getTorpedoState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $torpedoStorage->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);

        $this->storageRepository->shouldReceive('delete')
            ->with($storage)
            ->once();
        $this->torpedoStorageRepository->shouldReceive('delete')
            ->with($torpedoStorage)
            ->once();

        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::TORPEDO, true)
            ->once();

        $this->subject->clearTorpedoStorage($this->wrapper);
    }
}
