<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Mockery\MockInterface;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Lib\InformationWrapper;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\StuTestCase;

class CheckAstronomicalWaypointsTest extends StuTestCase
{
    /** @var MockInterface&AstroEntryRepositoryInterface */
    private MockInterface $astroEntryRepository;

    private CheckAstronomicalWaypointsInterface $subject;

    protected function setUp(): void
    {
        $this->astroEntryRepository = $this->mock(AstroEntryRepositoryInterface::class);

        $this->subject = new CheckAstronomicalWaypoints($this->astroEntryRepository);
    }

    public function testCheckWaypointExpectNothingWhenAstroStateOff(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $nextField = $this->mock(StarSystemMapInterface::class);
        $informations = $this->mock(InformationWrapper::class);

        $ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->subject->checkWaypoint(
            $ship,
            $nextField,
            $informations
        );
    }

    public function testCheckWaypointExpectNothingWhenNoAstroEntryPresent(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $nextField = $this->mock(StarSystemMapInterface::class);
        $informations = $this->mock(InformationWrapper::class);

        $ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $ship->shouldReceive('getSystemsId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->astroEntryRepository->shouldReceive('getByUserAndSystem')
            ->with(666, 42)
            ->once()
            ->andReturn(null);

        $this->subject->checkWaypoint(
            $ship,
            $nextField,
            $informations
        );
    }

    public static function provideCheckWaypointExpectArrivingWaypointData()
    {
        return [
            [1], [2], [3], [4], [5],
        ];
    }

    /**
     * @dataProvider provideCheckWaypointExpectArrivingWaypointData
     */
    public function testCheckWaypointExpectArrivingWaypoint(int $met): void
    {
        $ship = $this->mock(ShipInterface::class);
        $nextField = $this->mock(StarSystemMapInterface::class);
        $informations = $this->mock(InformationWrapper::class);
        $astroEntry = $this->mock(AstronomicalEntryInterface::class);

        $ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $ship->shouldReceive('getSystemsId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP');
        $ship->shouldReceive('getPosX')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $ship->shouldReceive('getPosY')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(5555);

        $astroEntry->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(AstronomicalMappingEnum::PLANNED);

        foreach (range(1, 5) as $number) {
            if ($number <= $met) {
                $astroEntry->shouldReceive('getStarsystemMap' . $number)
                    ->withNoArgs()
                    ->once()
                    ->andReturn($number === $met ? $nextField : null);
            }
        }

        $astroEntry->shouldReceive('setStarsystemMap' . $met)
            ->with(null)
            ->once();
        $astroEntry->shouldReceive('isMeasured')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->astroEntryRepository->shouldReceive('getByUserAndSystem')
            ->with(666, 42)
            ->once()
            ->andReturn($astroEntry);
        $this->astroEntryRepository->shouldReceive('save')
            ->with($astroEntry)
            ->once();

        $informations->shouldReceive('addInformation')
            ->with('Die SHIP hat einen Kartographierungs-Messpunkt erreicht (1|2)')
            ->once();

        $this->subject->checkWaypoint(
            $ship,
            $nextField,
            $informations
        );
    }

    public function testCheckWaypointExpectMeasured(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $nextField = $this->mock(StarSystemMapInterface::class);
        $informations = $this->mock(InformationWrapper::class);
        $astroEntry = $this->mock(AstronomicalEntryInterface::class);

        $ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $ship->shouldReceive('getSystemsId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $ship->shouldReceive('getPosX')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $ship->shouldReceive('getPosY')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(5555);

        $astroEntry->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(AstronomicalMappingEnum::PLANNED);

        $astroEntry->shouldReceive('getStarsystemMap1')
            ->withNoArgs()
            ->once()
            ->andReturn($nextField);

        $astroEntry->shouldReceive('setStarsystemMap1')
            ->with(null)
            ->once();
        $astroEntry->shouldReceive('isMeasured')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $astroEntry->shouldReceive('setState')
            ->with(AstronomicalMappingEnum::MEASURED)
            ->once();

        $this->astroEntryRepository->shouldReceive('getByUserAndSystem')
            ->with(666, 42)
            ->once()
            ->andReturn($astroEntry);
        $this->astroEntryRepository->shouldReceive('save')
            ->with($astroEntry)
            ->once();

        $informations->shouldReceive('addInformation')
            ->with('Die SHIP hat einen Kartographierungs-Messpunkt erreicht (1|2)')
            ->once();
        $informations->shouldReceive('addInformation')
            ->with('Die SHIP hat alle Kartographierungs-Messpunkte erreicht')
            ->once();

        $this->subject->checkWaypoint(
            $ship,
            $nextField,
            $informations
        );
    }

    public function testCheckWaypointExpectCancelOfFinalizing(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $nextField = $this->mock(StarSystemMapInterface::class);
        $informations = $this->mock(InformationWrapper::class);
        $astroEntry = $this->mock(AstronomicalEntryInterface::class);

        $ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $ship->shouldReceive('getSystemsId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING);
        $ship->shouldReceive('setAstroStartTurn')
            ->with(null)
            ->once();
        $ship->shouldReceive('setState')
            ->with(ShipStateEnum::SHIP_STATE_NONE)
            ->once();

        $astroEntry->shouldReceive('getState')
            ->withNoArgs()
            ->andReturn(AstronomicalMappingEnum::FINISHING);

        $astroEntry->shouldReceive('setState')
            ->with(AstronomicalMappingEnum::MEASURED)
            ->once();
        $astroEntry->shouldReceive('setAstroStartTurn')
            ->with(null)
            ->once();

        $this->astroEntryRepository->shouldReceive('getByUserAndSystem')
            ->with(666, 42)
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
            $nextField,
            $informations
        );
    }
}
