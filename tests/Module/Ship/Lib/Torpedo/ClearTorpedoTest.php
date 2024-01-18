<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Torpedo;

use Mockery\MockInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TorpedoStorageInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TorpedoStorageRepositoryInterface;
use Stu\StuTestCase;

class ClearTorpedoTest extends StuTestCase
{
    /** @var MockInterface&ShipSystemManagerInterface */
    private ShipSystemManagerInterface $shipSystemManager;

    /** @var MockInterface&TorpedoStorageRepositoryInterface */
    private TorpedoStorageRepositoryInterface $torpedoStorageRepository;

    /** @var MockInterface&StorageRepositoryInterface */
    private StorageRepositoryInterface $storageRepository;

    /** @var MockInterface&ShipWrapperInterface */
    private ShipWrapperInterface $wrapper;
    /** @var MockInterface&ShipInterface */
    private ShipInterface $ship;

    private ClearTorpedoInterface $subject;

    public function setUp(): void
    {
        //injected
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->torpedoStorageRepository = $this->mock(TorpedoStorageRepositoryInterface::class);
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);

        //params
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new ClearTorpedo(
            $this->shipSystemManager,
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
        $torpedoStorage = $this->mock(TorpedoStorageInterface::class);
        $storage = $this->mock(StorageInterface::class);

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
        $torpedoStorage = $this->mock(TorpedoStorageInterface::class);
        $storage = $this->mock(StorageInterface::class);

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

        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_TORPEDO, true)
            ->once();

        $this->subject->clearTorpedoStorage($this->wrapper);
    }
}
