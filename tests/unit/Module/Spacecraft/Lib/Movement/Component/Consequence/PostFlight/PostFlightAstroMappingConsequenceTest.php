<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Data\AstroLaboratorySystemData;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
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
    private $astroEntryRepository;
    /** @var MockInterface&AstroEntryLibInterface */
    private $astroEntryLib;
    /** @var MockInterface&CreatePrestigeLogInterface */
    private $createPrestigeLog;
    /** @var MockInterface&MessageFactoryInterface */
    private $messageFactory;

    private FlightConsequenceInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&FlightRouteInterface */
    private MockInterface $flightRoute;

    #[Override]
    protected function setUp(): void
    {
        $this->astroEntryRepository = $this->mock(AstroEntryRepositoryInterface::class);
        $this->astroEntryLib = $this->mock(AstroEntryLibInterface::class);
        $this->createPrestigeLog = $this->mock(CreatePrestigeLogInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new PostFlightAstroMappingConsequence(
            $this->astroEntryRepository,
            $this->astroEntryLib,
            $this->createPrestigeLog,
            $this->messageFactory
        );
    }

    public function testTriggerExpectNothingWhenShipDestroyed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

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
        $messages = $this->mock(MessageCollectionInterface::class);

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

        $messages = $this->mock(MessageCollectionInterface::class);

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

        $messages = $this->mock(MessageCollectionInterface::class);

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

        $this->astroEntryLib->shouldReceive('getAstroEntryByShipLocation')
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
        $messages = $this->mock(MessageCollectionInterface::class);
        $astroEntry = $this->mock(AstronomicalEntryInterface::class);
        $astroLab = $this->mock(AstroLaboratorySystemData::class);
        $message = $this->mock(MessageInterface::class);

        $this->wrapper->shouldReceive('getAstroLaboratorySystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($astroLab);

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
        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($map);
        $this->ship->shouldReceive('getSectorString')
            ->withNoArgs()
            ->andReturn('SECTOR');
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::SHIP_STATE_NONE);

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

        $this->astroEntryLib->shouldReceive('getAstroEntryByShipLocation')
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

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with(null, 123)
            ->once()
            ->andReturn($message);

        $message->shouldReceive('add')
            ->with('Die SHIP hat einen Kartographierungs-Messpunkt erreicht: SECTOR')
            ->once()
            ->andReturn($message);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectMeasured(): void
    {

        $map = $this->mock(MapInterface::class);
        $user = $this->mock(UserInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $astroEntry = $this->mock(AstronomicalEntryInterface::class);
        $astroLab = $this->mock(AstroLaboratorySystemData::class);
        $message = $this->mock(MessageInterface::class);

        $this->wrapper->shouldReceive('getAstroLaboratorySystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($astroLab);

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
        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($map);
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::SHIP_STATE_NONE);
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

        $this->astroEntryLib->shouldReceive('getAstroEntryByShipLocation')
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


        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with(null, 123)
            ->once()
            ->andReturn($message);

        $message->shouldReceive('add')
            ->with('Die SHIP hat alle Kartographierungs-Messpunkte erreicht')
            ->once()
            ->andReturn($message);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectCancelOfFinalizing(): void
    {
        $map = $this->mock(MapInterface::class);
        $user = $this->mock(UserInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $astroEntry = $this->mock(AstronomicalEntryInterface::class);
        $astroLab = $this->mock(AstroLaboratorySystemData::class);
        $message = $this->mock(MessageInterface::class);

        $this->wrapper->shouldReceive('getAstroLaboratorySystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($astroLab);

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
        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($map);
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::SHIP_STATE_ASTRO_FINALIZING);

        $this->ship->shouldReceive('setState')
            ->with(SpacecraftStateEnum::SHIP_STATE_NONE)
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

        $astroLab->shouldReceive('setAstroStartTurn')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $astroLab->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->astroEntryLib->shouldReceive('getAstroEntryByShipLocation')
            ->with($this->ship, false)
            ->once()
            ->andReturn($astroEntry);
        $this->astroEntryRepository->shouldReceive('save')
            ->with($astroEntry)
            ->once();

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with(null, 123)
            ->once()
            ->andReturn($message);

        $message->shouldReceive('add')
            ->with('Die SHIP hat das Finalisieren der Kartographierung abgebrochen')
            ->once()
            ->andReturn($message);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }
}
