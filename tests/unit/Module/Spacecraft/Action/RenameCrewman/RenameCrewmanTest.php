<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\RenameCrewman;

use Mockery\MockInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Module\Spacecraft\View\ShowCrewmanDetails\ShowCrewmanDetails;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class RenameCrewmanTest extends ActionControllerTestCase
{
    private MockInterface&CrewRepositoryInterface $crewRepository;

    private RenameCrewman $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->subject = new RenameCrewman($this->crewRepository);
    }

    public function testRenamesOwnCrewman(): void
    {
        $user = $this->mock(User::class);
        $crew = $this->mock(Crew::class);
        request::setMockVars(['id' => 42, 'name' => 'Neuer Name']);

        $this->game->shouldReceive('setView')->with(ShowCrewmanDetails::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->andReturn($user);
        $this->game->shouldReceive('getInfo->addInformation')->with('Der Crewmanname wurde geändert')->once();
        $this->crewRepository->shouldReceive('find')->with(42)->andReturn($crew)->once();
        $this->crewRepository->shouldReceive('save')->with($crew)->once();
        $user->shouldReceive('getId')->andReturn(101);
        $crew->shouldReceive('getUser')->andReturn($user);
        $crew->shouldReceive('setName')->with('Neuer Name')->once();

        $this->subject->handle($this->game);
    }
}
