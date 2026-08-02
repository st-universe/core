<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Torpedo;

use Doctrine\Common\Collections\ArrayCollection;
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
        $this->ship->shouldReceive('getTorpedoStorages')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

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
        $this->ship->shouldReceive('getTorpedoStorages')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$torpedoStorage]));
        $this->ship->shouldReceive('removeTorpedoStorage')
            ->with($torpedoStorage)
            ->once();
        $this->ship->shouldReceive('getTorpedoState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $torpedoStorage->shouldReceive('isActive')
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
        $this->ship->shouldReceive('getTorpedoStorages')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$torpedoStorage]));
        $this->ship->shouldReceive('removeTorpedoStorage')
            ->with($torpedoStorage)
            ->once();
        $this->ship->shouldReceive('getTorpedoState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getFireableTorpedoStorages')
            ->withNoArgs()
            ->once()
            ->andReturn([]);
        $this->ship->shouldReceive('getTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $torpedoStorage->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $torpedoStorage->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

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

    public function testClearingActiveTorpedoSelectsAnotherFireableType(): void
    {
        $activeStorage = $this->mock(TorpedoStorage::class);
        $replacementStorage = $this->mock(TorpedoStorage::class);
        $storage = $this->mock(Storage::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('removeTorpedoStorage')
            ->with($activeStorage)
            ->once();
        $this->ship->shouldReceive('getFireableTorpedoStorages')
            ->withNoArgs()
            ->once()
            ->andReturn([$replacementStorage]);
        $this->ship->shouldReceive('getTorpedoState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $activeStorage->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $activeStorage->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $replacementStorage->shouldReceive('setActive')
            ->with(true)
            ->once();

        $this->storageRepository->shouldReceive('delete')
            ->with($storage)
            ->once();
        $this->torpedoStorageRepository->shouldReceive('delete')
            ->with($activeStorage)
            ->once();
        $this->torpedoStorageRepository->shouldReceive('save')
            ->with($replacementStorage)
            ->once();

        $this->subject->clearTorpedoStorage($this->wrapper, $activeStorage);
    }
}
