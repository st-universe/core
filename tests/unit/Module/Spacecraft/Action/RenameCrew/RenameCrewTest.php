<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\RenameCrew;

use Mockery\MockInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowRenameCrew\ShowRenameCrew;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewRepositoryInterface;

class RenameCrewTest extends ActionControllerTestCase
{
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&CrewRepositoryInterface $crewRepository;
    private MockInterface&RenameCrewRequestInterface $renameCrewRequest;

    private RenameCrew $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->renameCrewRequest = $this->mock(RenameCrewRequestInterface::class);

        $this->subject = new RenameCrew(
            $this->spacecraftLoader,
            $this->crewRepository,
            $this->renameCrewRequest
        );
    }

    public function testGuestCanRenameOwnCrewViaUplink(): void
    {
        $user = $this->mock(User::class);
        $crew = $this->mock(Crew::class);

        $shipId = 42;
        $crewId = 23;
        $userId = 101;
        request::setMockVars(['id' => $shipId, 'crewid' => $crewId]);

        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('setView')
            ->with(ShowRenameCrew::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->with('CREW', $crew)
            ->once();

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($userId);
        $crew->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($crewId);
        $crew->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->spacecraftLoader->shouldReceive('getByIdAndUser')
            ->with($shipId, $userId, true)
            ->once();
        $this->crewRepository->shouldReceive('find')
            ->with($crewId)
            ->once()
            ->andReturn($crew);
        $this->crewRepository->shouldReceive('save')
            ->with($crew)
            ->once();
        $this->renameCrewRequest->shouldReceive('getName')
            ->with($crewId)
            ->once()
            ->andReturn('NEUER NAME');
        $crew->shouldReceive('setName')
            ->with('NEUER NAME')
            ->once();

        $this->subject->handle($this->game);
    }
}
