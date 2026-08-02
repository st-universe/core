<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Mockery\MockInterface;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TorpedoType;
use Stu\StuTestCase;

class ManageTorpedoReservesTest extends StuTestCase
{
    private MockInterface&ShipTorpedoManagerInterface $shipTorpedoManager;
    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&Ship $ship;
    private MockInterface&ManagerProviderInterface $managerProvider;
    private MockInterface&TorpedoType $torpedoType;

    private ManageTorpedoReserves $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->shipTorpedoManager = $this->mock(ShipTorpedoManagerInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(Ship::class);
        $this->managerProvider = $this->mock(ManagerProviderInterface::class);
        $this->torpedoType = $this->mock(TorpedoType::class);

        $this->subject = new ManageTorpedoReserves($this->shipTorpedoManager);
    }

    public function testLoadsNonFireableTorpedoIntoDedicatedReserve(): void
    {
        $rump = $this->mock(SpacecraftRump::class);
        $commodity = $this->mock(Commodity::class);
        $sourceStorage = $this->mock(Storage::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getPossibleTorpedoTypes')
            ->withNoArgs()
            ->once()
            ->andReturn([5 => $this->torpedoType]);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('isTorpedoStorageHealthy')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getRump')
            ->withNoArgs()
            ->once()
            ->andReturn($rump);
        $this->ship->shouldReceive('getTorpedoStorageForType')
            ->with($this->torpedoType)
            ->once()
            ->andReturn(null);
        $this->ship->shouldReceive('getMaxTorpedos')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $this->ship->shouldReceive('getTotalTorpedoCount')
            ->withNoArgs()
            ->once()
            ->andReturn(20);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('ship');

        $rump->shouldReceive('getTorpedoLevel')
            ->withNoArgs()
            ->once()
            ->andReturn(4);

        $this->torpedoType->shouldReceive('getLevel')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $this->torpedoType->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $this->torpedoType->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);
        $this->torpedoType->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('reserve torpedo');

        $this->managerProvider->shouldReceive('getStorage->get')
            ->with(1)
            ->once()
            ->andReturn($sourceStorage);
        $this->managerProvider->shouldReceive('lowerStorage')
            ->with($commodity, 10)
            ->once();
        $sourceStorage->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(50);

        $this->shipTorpedoManager->shouldReceive('changeTorpedo')
            ->with($this->wrapper, 10, $this->torpedoType)
            ->once();

        $result = $this->subject->manage(
            $this->wrapper,
            ['torp_reserve' => [42 => [5 => '10']]],
            $this->managerProvider
        );

        $this->assertSame(['ship: Es wurden 10 Torpedos des Typs reserve torpedo in das Torpedolager geladen'], $result);
    }
}
