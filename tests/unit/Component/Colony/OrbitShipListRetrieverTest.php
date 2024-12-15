<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class OrbitShipListRetrieverTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftRepositoryInterface */
    private $spacecraftRepository;

    private OrbitShipListRetriever $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);

        $this->subject = new OrbitShipListRetriever(
            $this->spacecraftRepository
        );
    }

    public function testRetrieveReturnsData(): void
    {
        $ship1 = $this->mock(ShipInterface::class);
        $ship2 = $this->mock(ShipInterface::class);
        $fleet = $this->mock(FleetInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $starSystemMap = $this->mock(StarSystemMapInterface::class);

        $fleetId = 666;
        $fleetName = 'some-fleet';
        $shipId1 = 42;
        $shipId2 = 21;

        $colony->shouldReceive('getStarsystemMap')
            ->withNoArgs()
            ->once()
            ->andReturn($starSystemMap);

        $this->spacecraftRepository->shouldReceive('getByLocation')
            ->with($starSystemMap)
            ->once()
            ->andReturn([$ship1, $ship2]);

        $ship1->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId1);
        $ship1->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($fleet);

        $ship2->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $ship2->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId2);

        $fleet->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($fleetId);
        $fleet->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($fleetName);

        static::assertSame(
            [
                $fleetId => ['ships' => [$shipId1 => $ship1], 'name' => $fleetName],
                0 => ['ships' => [$shipId2 => $ship2], 'name' => 'Einzelschiffe'],
            ],
            $this->subject->retrieve($colony)
        );
    }
}
