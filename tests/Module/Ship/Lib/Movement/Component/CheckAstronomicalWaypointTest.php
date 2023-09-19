<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use Mockery\MockInterface;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Lib\InformationWrapper;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\StuTestCase;

class CheckAstronomicalWaypointTest extends StuTestCase
{
    /** @var MockInterface&AstroEntryRepositoryInterface */
    private MockInterface $astroEntryRepository;

    private CheckAstronomicalWaypointInterface $subject;

    protected function setUp(): void
    {
        $this->astroEntryRepository = $this->mock(AstroEntryRepositoryInterface::class);

        $this->subject = new CheckAstronomicalWaypoint($this->astroEntryRepository);
    }

    public function testCheckWaypointExpectNothingWhenAstroStateOff(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $informations = $this->mock(InformationWrapper::class);

        $ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->subject->checkWaypoint(
            $ship,
            $informations
        );
    }

    public function testCheckWaypointExpectNothingWhenNoAstroEntryPresent(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $informations = $this->mock(InformationWrapper::class);

        $ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->astroEntryRepository->shouldReceive('getByShipLocation')
            ->with($ship)
            ->once()
            ->andReturn(null);

        $this->subject->checkWaypoint(
            $ship,
            $informations
        );
    }

    public function testCheckWaypointExpectArrivingWaypoint(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $map = $this->mock(MapInterface::class);
        $informations = $this->mock(InformationWrapper::class);
        $astroEntry = $this->mock(AstronomicalEntryInterface::class);

        $ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP');
        $ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($map);
        $ship->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');
        $ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(5555);

        $map->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $astroEntry->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(AstronomicalMappingEnum::PLANNED);
        $astroEntry->shouldReceive('getFieldIds')
            ->withNoArgs()
            ->once()
            ->andReturn("a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}");

        $astroEntry->shouldReceive('setFieldIds')
            ->with("a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}")
            ->once();
        $astroEntry->shouldReceive('isMeasured')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->astroEntryRepository->shouldReceive('getByShipLocation')
            ->with($ship)
            ->once()
            ->andReturn($astroEntry);
        $this->astroEntryRepository->shouldReceive('save')
            ->with($astroEntry)
            ->once();

        $informations->shouldReceive('addInformation')
            ->with('Die SHIP hat einen Kartographierungs-Messpunkt erreicht: SECTOR')
            ->once();

        $this->subject->checkWaypoint(
            $ship,
            $informations
        );
    }

    public function testCheckWaypointExpectMeasured(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $map = $this->mock(MapInterface::class);
        $informations = $this->mock(InformationWrapper::class);
        $astroEntry = $this->mock(AstronomicalEntryInterface::class);

        $ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($map);
        $ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(5555);

        $map->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $astroEntry->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(AstronomicalMappingEnum::PLANNED);
        $astroEntry->shouldReceive('getFieldIds')
            ->withNoArgs()
            ->once()
            ->andReturn("a:1:{i:0;i:5;}");
        $astroEntry->shouldReceive('setFieldIds')
            ->with("")
            ->once();
        $astroEntry->shouldReceive('isMeasured')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $astroEntry->shouldReceive('setState')
            ->with(AstronomicalMappingEnum::MEASURED)
            ->once();

        $this->astroEntryRepository->shouldReceive('getByShipLocation')
            ->with($ship)
            ->once()
            ->andReturn($astroEntry);
        $this->astroEntryRepository->shouldReceive('save')
            ->with($astroEntry)
            ->once();

        $informations->shouldReceive('addInformation')
            ->with('Die SHIP hat alle Kartographierungs-Messpunkte erreicht')
            ->once();

        $this->subject->checkWaypoint(
            $ship,
            $informations
        );
    }

    public function testCheckWaypointExpectCancelOfFinalizing(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $map = $this->mock(MapInterface::class);
        $informations = $this->mock(InformationWrapper::class);
        $astroEntry = $this->mock(AstronomicalEntryInterface::class);

        $ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($map);
        $ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING);
        $ship->shouldReceive('setAstroStartTurn')
            ->with(null)
            ->once();
        $ship->shouldReceive('setState')
            ->with(ShipStateEnum::SHIP_STATE_NONE)
            ->once();

        $map->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $astroEntry->shouldReceive('getState')
            ->withNoArgs()
            ->andReturn(AstronomicalMappingEnum::FINISHING);

        $astroEntry->shouldReceive('setState')
            ->with(AstronomicalMappingEnum::MEASURED)
            ->once();
        $astroEntry->shouldReceive('setAstroStartTurn')
            ->with(null)
            ->once();

        $this->astroEntryRepository->shouldReceive('getByShipLocation')
            ->with($ship)
            ->once()
            ->andReturn($astroEntry);
        $this->astroEntryRepository->shouldReceive('save')
            ->with($astroEntry)
            ->once();

        $informations->shouldReceive('addInformation')
            ->with('Die SHIP hat das Finalisieren der Kartographierung abgebrochen')
            ->once();

        $this->subject->checkWaypoint(
            $ship,
            $informations
        );
    }
}
