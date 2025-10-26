<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\CancelResearch;

use Mockery\MockInterface;
use request;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuTestCase;

class CancelResearchTest extends StuTestCase
{
    private MockInterface&ResearchedRepositoryInterface $researchedRepository;
    private MockInterface&ComponentRegistrationInterface $componentRegistration;

    private CancelResearch $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);
        $this->componentRegistration = $this->mock(ComponentRegistrationInterface::class);

        $this->subject = new CancelResearch(
            $this->researchedRepository,
            $this->componentRegistration
        );
    }

    public function testHandleDoesNothingIfNoCurrentResearch(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);

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
        $user = $this->mock(User::class);
        $researchReference = $this->mock(Researched::class);

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

        $game->shouldReceive('getInfo->addInformation')
            ->with('Die laufende Forschung wurde abgebrochen')
            ->once();
        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('setView')
            ->with(GameController::DEFAULT_VIEW)
            ->once();

        $this->componentRegistration->shouldReceive('addComponentUpdate')
            ->with(GameComponentEnum::RESEARCH)
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
