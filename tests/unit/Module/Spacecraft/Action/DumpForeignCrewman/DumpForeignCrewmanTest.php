<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DumpForeignCrewman;

use Mockery\MockInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

class DumpForeignCrewmanTest extends ActionControllerTestCase
{
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&CrewAssignmentRepositoryInterface $crewAssignmentRepository;
    private MockInterface&SpacecraftLeaverInterface $spacecraftLeaver;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;

    private DumpForeignCrewman $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->crewAssignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->spacecraftLeaver = $this->mock(SpacecraftLeaverInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->subject = new DumpForeignCrewman(
            $this->spacecraftLoader,
            $this->crewAssignmentRepository,
            $this->spacecraftLeaver,
            $this->privateMessageSender
        );
    }

    public function testGuestCanRemoveOwnCrewViaUplink(): void
    {
        $guest = $this->mock(User::class);
        $stationOwner = $this->mock(User::class);
        $ship = $this->mock(Spacecraft::class);
        $crew = $this->mock(Crew::class);
        $crewAssignment = $this->mock(CrewAssignment::class);
        $information = $this->mock(InformationWrapper::class);

        $shipId = 42;
        $crewId = 23;
        $guestId = 101;
        $stationOwnerId = 202;
        request::setMockVars(['id' => $shipId, 'crewid' => $crewId]);

        $this->game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($guest);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($information);

        $guest->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($guestId);
        $guest->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('GAST');
        $stationOwner->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($stationOwnerId);

        $this->spacecraftLoader->shouldReceive('getByIdAndUser')
            ->with($shipId, $guestId, true)
            ->once()
            ->andReturn($ship);
        $this->crewAssignmentRepository->shouldReceive('find')
            ->with($crewId)
            ->once()
            ->andReturn($crewAssignment);
        $this->spacecraftLeaver->shouldReceive('leaveSpacecraft')
            ->with($crewAssignment)
            ->once()
            ->andReturn('Der Crewman hat das Schiff in einer Rettungskapsel verlassen!');
        $this->spacecraftLeaver->shouldReceive('dumpCrewman')
            ->never();
        $this->privateMessageSender->shouldReceive('send')
            ->with(
                $guestId,
                $stationOwnerId,
                'Spieler GAST hat seinen Crewman CREWMAN von der Station STATION entfernt.'
            )
            ->once();

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipId);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($stationOwner);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('STATION');
        $crewAssignment->shouldReceive('getSpacecraft')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);
        $crewAssignment->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn($crew);
        $crew->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($guest);
        $crew->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('CREWMAN');

        $information->shouldReceive('addInformation')
            ->with('Der Crewman hat das Schiff in einer Rettungskapsel verlassen!')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testStationOwnerCanRemoveForeignCrew(): void
    {
        $stationOwner = $this->mock(User::class);
        $guest = $this->mock(User::class);
        $ship = $this->mock(Spacecraft::class);
        $crew = $this->mock(Crew::class);
        $crewAssignment = $this->mock(CrewAssignment::class);
        $information = $this->mock(InformationWrapper::class);

        $shipId = 42;
        $crewId = 23;
        $stationOwnerId = 101;
        $guestId = 202;
        request::setMockVars(['id' => $shipId, 'crewid' => $crewId]);

        $this->game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($stationOwner);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($information);

        $stationOwner->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($stationOwnerId);
        $stationOwner->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('BESITZER');
        $guest->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($guestId);

        $this->spacecraftLoader->shouldReceive('getByIdAndUser')
            ->with($shipId, $stationOwnerId, true)
            ->once()
            ->andReturn($ship);
        $this->crewAssignmentRepository->shouldReceive('find')
            ->with($crewId)
            ->once()
            ->andReturn($crewAssignment);
        $this->spacecraftLeaver->shouldReceive('dumpCrewman')
            ->with(
                $crewAssignment,
                'Die Dienste von Crewman CREWMAN werden nicht mehr auf der Station STATION von Spieler BESITZER benötigt.'
            )
            ->once()
            ->andReturn('Der Crewman hat das Schiff in einer Rettungskapsel verlassen!');
        $this->spacecraftLeaver->shouldReceive('leaveSpacecraft')
            ->never();
        $this->privateMessageSender->shouldReceive('send')
            ->never();

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipId);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($stationOwner);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('STATION');
        $crewAssignment->shouldReceive('getSpacecraft')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);
        $crewAssignment->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn($crew);
        $crew->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($guest);
        $crew->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('CREWMAN');

        $information->shouldReceive('addInformation')
            ->with('Der Crewman hat das Schiff in einer Rettungskapsel verlassen!')
            ->once();

        $this->subject->handle($this->game);
    }
}
