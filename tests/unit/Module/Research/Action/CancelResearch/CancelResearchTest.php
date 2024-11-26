<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\CancelResearch;

use Mockery\MockInterface;
use Override;
use request;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuTestCase;

class CancelResearchTest extends StuTestCase
{
    private MockInterface $researchedRepository;

    private CancelResearch $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);

        $this->subject = new CancelResearch(
            $this->researchedRepository
        );
    }

    public function testHandleDoesNothingIfNoCurrentResearch(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(UserInterface::class);

        request::setMockVars(['id' => 42]);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([]);

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('setView')
            ->with(GameController::DEFAULT_VIEW)
            ->once();

        $this->subject->handle($game);
    }

    public function testHandleCancelsTheUsersResearch(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(UserInterface::class);
        $researchReference = $this->mock(ResearchedInterface::class);

        request::setMockVars(['id' => 42]);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([$researchReference]);
        $this->researchedRepository->shouldReceive('delete')
            ->with($researchReference)
            ->once();

        $researchReference->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $game->shouldReceive('addInformation')
            ->with('Die laufende Forschung wurde abgebrochen')
            ->once();
        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('setView')
            ->with(GameController::DEFAULT_VIEW)
            ->once();

        $this->subject->handle($game);
    }

    public function testPerformSessionCheckReturnsTrue(): void
    {
        $this->assertTrue(
            $this->subject->performSessionCheck()
        );
    }
}
