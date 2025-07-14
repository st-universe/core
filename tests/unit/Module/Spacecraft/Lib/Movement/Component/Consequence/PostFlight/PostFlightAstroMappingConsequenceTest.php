<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Ship\AstronomicalMappingStateEnum;
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
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\StuTestCase;

class PostFlightAstroMappingConsequenceTest extends StuTestCase
{
    private MockInterface&AstroEntryRepositoryInterface $astroEntryRepository;
    private MockInterface&AstroEntryLibInterface $astroEntryLib;
    private MockInterface&CreatePrestigeLogInterface $createPrestigeLog;
    private MockInterface&MessageFactoryInterface $messageFactory;

    private FlightConsequenceInterface $subject;

    private MockInterface&Ship $ship;

    private MockInterface&ShipWrapperInterface $wrapper;

    private MockInterface&FlightRouteInterface $flightRoute;

    #[Override]
    protected function setUp(): void
    {
        $this->astroEntryRepository = $this->mock(AstroEntryRepositoryInterface::class);
        $this->astroEntryLib = $this->mock(AstroEntryLibInterface::class);
        $this->createPrestigeLog = $this->mock(CreatePrestigeLogInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->ship = $this->mock(Ship::class);
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

        $this->ship->shouldReceive('getCondition->isDestroyed')
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

        $this->ship->shouldReceive('getCondition->isDestroyed')
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

        $this->ship->shouldReceive('getCondition->isDestroyed')
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

        $this->ship->shouldReceive('getCondition->isDestroyed')
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
            ->andReturn($this->mock(StarSystem::class));

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
        $map = $this->mock(Map::class);
        $user = $this->mock(User::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $astroEntry = $this->mock(AstronomicalEntry::class);
        $astroLab = $this->mock(AstroLaboratorySystemData::class);
        $message = $this->mock(MessageInterface::class);

        $this->wrapper->shouldReceive('getAstroLaboratorySystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($astroLab);

        $this->ship->shouldReceive('getCondition->isDestroyed')
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
            ->andReturn($this->mock(StarSystem::class));
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
            ->andReturn(SpacecraftStateEnum::NONE);

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
            ->andReturn(AstronomicalMappingStateEnum::PLANNED);
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

        $map = $this->mock(Map::class);
        $user = $this->mock(User::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $astroEntry = $this->mock(AstronomicalEntry::class);
        $astroLab = $this->mock(AstroLaboratorySystemData::class);
        $message = $this->mock(MessageInterface::class);

        $this->wrapper->shouldReceive('getAstroLaboratorySystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($astroLab);

        $this->ship->shouldReceive('getCondition->isDestroyed')
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
            ->andReturn($this->mock(StarSystem::class));
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
            ->andReturn(SpacecraftStateEnum::NONE);
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
            ->andReturn(AstronomicalMappingStateEnum::PLANNED);
        $astroEntry->shouldReceive('getFieldIds')
            ->withNoArgs()
            ->once()
            ->andReturn("a:1:{i:0;i:5;}");
        $astroEntry->shouldReceive('setFieldIds')
            ->with("")
            ->once();
        $astroEntry->shouldReceive('setState')
            ->with(AstronomicalMappingStateEnum::MEASURED)
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
        $map = $this->mock(Map::class);
        $user = $this->mock(User::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $astroEntry = $this->mock(AstronomicalEntry::class);
        $astroLab = $this->mock(AstroLaboratorySystemData::class);
        $message = $this->mock(MessageInterface::class);

        $this->wrapper->shouldReceive('getAstroLaboratorySystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($astroLab);

        $this->ship->shouldReceive('getCondition->isDestroyed')
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
            ->andReturn($this->mock(StarSystem::class));
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
            ->andReturn(SpacecraftStateEnum::ASTRO_FINALIZING);

        $this->ship->shouldReceive('getCondition->setState')
            ->with(SpacecraftStateEnum::NONE)
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
            ->andReturn(AstronomicalMappingStateEnum::FINISHING);

        $astroEntry->shouldReceive('setState')
            ->with(AstronomicalMappingStateEnum::MEASURED)
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
