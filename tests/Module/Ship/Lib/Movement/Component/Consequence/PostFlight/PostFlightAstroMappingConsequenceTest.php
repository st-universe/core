<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\PostFlight;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\StuTestCase;

class PostFlightAstroMappingConsequenceTest extends StuTestCase
{
    /** @var MockInterface&AstroEntryRepositoryInterface */
    private MockInterface $astroEntryRepository;

    /** @var MockInterface&CreatePrestigeLogInterface */
    private MockInterface $createPrestigeLog;

    private FlightConsequenceInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&FlightRouteInterface */
    private MockInterface $flightRoute;

    protected function setUp(): void
    {
        $this->astroEntryRepository = $this->mock(AstroEntryRepositoryInterface::class);
        $this->createPrestigeLog = $this->mock(CreatePrestigeLogInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new PostFlightAstroMappingConsequence(
            $this->astroEntryRepository,
            $this->createPrestigeLog
        );
    }

    public function testTriggerExpectNothingWhenShipDestroyed(): void
    {
        $messages = $this->mock(FightMessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectNothingWhenAstroStateOff(): void
    {
        $messages = $this->mock(FightMessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectNothingWhenNeitherInSystemNorInRegion(): void
    {

        $messages = $this->mock(FightMessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->ship->shouldReceive('getMapRegion')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectNothingWhenNoAstroEntryPresent(): void
    {

        $messages = $this->mock(FightMessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(StarSystemInterface::class));

        $this->astroEntryRepository->shouldReceive('getByShipLocation')
            ->with($this->ship, false)
            ->once()
            ->andReturn(null);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectArrivingWaypoint(): void
    {

        $map = $this->mock(MapInterface::class);
        $user = $this->mock(UserInterface::class);
        $messages = $this->mock(FightMessageCollectionInterface::class);
        $astroEntry = $this->mock(AstronomicalEntryInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(StarSystemInterface::class));
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($map);
        $this->ship->shouldReceive('getSectorString')
            ->withNoArgs()
            ->andReturn('SECTOR');
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(5555);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

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

        $this->astroEntryRepository->shouldReceive('getByShipLocation')
            ->with($this->ship, false)
            ->once()
            ->andReturn($astroEntry);
        $this->astroEntryRepository->shouldReceive('save')
            ->with($astroEntry)
            ->once();

        $this->createPrestigeLog->shouldReceive('createLog')
            ->with(
                1,
                '1 Prestige erhalten für Kartographierungs-Messpunkt "SECTOR"',
                $user,
                Mockery::any()
            )
            ->once();

        $message = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (FightMessageInterface $m) use (&$message) {

                $message = $m;

                return $m->getRecipientId() === 123;
            }))
            ->once();

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );

        $this->assertEquals(['Die SHIP hat einen Kartographierungs-Messpunkt erreicht: SECTOR'], $message->getMessage());
    }

    public function testTriggerExpectMeasured(): void
    {

        $map = $this->mock(MapInterface::class);
        $user = $this->mock(UserInterface::class);
        $messages = $this->mock(FightMessageCollectionInterface::class);
        $astroEntry = $this->mock(AstronomicalEntryInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(StarSystemInterface::class));
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($map);
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(5555);
        $this->ship->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');

        $map->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

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
        $astroEntry->shouldReceive('setState')
            ->with(AstronomicalMappingEnum::MEASURED)
            ->once();

        $this->astroEntryRepository->shouldReceive('getByShipLocation')
            ->with($this->ship, false)
            ->once()
            ->andReturn($astroEntry);
        $this->astroEntryRepository->shouldReceive('save')
            ->with($astroEntry)
            ->once();

        $this->createPrestigeLog->shouldReceive('createLog')
            ->with(
                1,
                '1 Prestige erhalten für Kartographierungs-Messpunkt "SECTOR"',
                $user,
                Mockery::any()
            )
            ->once();


        $message = null;
        $messages->shouldReceive('add')
            ->with(Mockery::on(function (FightMessageInterface $m) use (&$message) {

                $message = $m;

                return $m->getRecipientId() === 123;
            }))
            ->once();

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );

        $this->assertEquals(['Die SHIP hat alle Kartographierungs-Messpunkte erreicht'], $message->getMessage());
    }

    public function testTriggerExpectCancelOfFinalizing(): void
    {
        $map = $this->mock(MapInterface::class);
        $user = $this->mock(UserInterface::class);
        $messages = $this->mock(FightMessageCollectionInterface::class);
        $astroEntry = $this->mock(AstronomicalEntryInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getAstroState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(StarSystemInterface::class));
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($map);
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING);
        $this->ship->shouldReceive('setAstroStartTurn')
            ->with(null)
            ->once();
        $this->ship->shouldReceive('setState')
            ->with(ShipStateEnum::SHIP_STATE_NONE)
            ->once();

        $map->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

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
            ->with($this->ship, false)
            ->once()
            ->andReturn($astroEntry);
        $this->astroEntryRepository->shouldReceive('save')
            ->with($astroEntry)
            ->once();

        $message = null;

        $messages->shouldReceive('add')
            ->with(Mockery::on(function (FightMessageInterface $m) use (&$message) {

                $message = $m;

                return $m->getRecipientId() === 123;
            }))
            ->once();

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );

        $this->assertEquals(['Die SHIP hat das Finalisieren der Kartographierung abgebrochen'], $message->getMessage());
    }
}
