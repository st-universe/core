<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\SetTutorial;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\View\Noop\Noop;
use Stu\Orm\Entity\TutorialStep;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserTutorial;
use Stu\Orm\Repository\TutorialStepRepositoryInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;
use Stu\StuTestCase;

class SetTutorialTest extends StuTestCase
{
    private MockInterface&TutorialStepRepositoryInterface $tutorialStepRepository;

    private MockInterface&UserTutorialRepositoryInterface $userTutorialRepository;

    private MockInterface&GameControllerInterface $game;

    private MockInterface&User $user;

    private ActionControllerInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->tutorialStepRepository = $this->mock(TutorialStepRepositoryInterface::class);
        $this->userTutorialRepository = $this->mock(UserTutorialRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->user = $this->mock(User::class);

        $this->subject = new SetTutorial(
            $this->tutorialStepRepository,
            $this->userTutorialRepository
        );
    }

    public function testHandleSavesFollowingStepWhenCurrentTutorialIsActive(): void
    {
        $currentStep = $this->mockTutorialStep(27);
        $nextStep = $this->mockTutorialStep(28);
        $userTutorial = $this->mock(UserTutorial::class);

        $currentStep->shouldReceive('getNextStep')
            ->withNoArgs()
            ->once()
            ->andReturn($nextStep);
        $currentStep->shouldReceive('getPreviousStep')
            ->withNoArgs()
            ->never();

        $this->expectCommonCalls($currentStep, new ArrayCollection([27 => $userTutorial]));

        $userTutorial->shouldReceive('setTutorialStep')
            ->with($nextStep)
            ->once()
            ->andReturnSelf();

        $this->userTutorialRepository->shouldReceive('save')
            ->with($userTutorial)
            ->once();

        request::setMockVars([
            'currentstep' => 27,
            'isforward' => 1
        ]);

        $this->subject->handle($this->game);
    }

    public function testHandleSkipsStaleForwardRequestWhenTutorialAlreadyAdvanced(): void
    {
        $currentStep = $this->mockTutorialStep(27);
        $previousStep = $this->mockTutorialStep(26);
        $nextStep = $this->mockTutorialStep(28);
        $userTutorial = $this->mock(UserTutorial::class);

        $currentStep->shouldReceive('getPreviousStep')
            ->withNoArgs()
            ->once()
            ->andReturn($previousStep);
        $previousStep->shouldReceive('getPreviousStep')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $currentStep->shouldReceive('getNextStep')
            ->withNoArgs()
            ->once()
            ->andReturn($nextStep);

        $this->expectCommonCalls($currentStep, new ArrayCollection([28 => $userTutorial]));

        $userTutorial->shouldReceive('setTutorialStep')
            ->never();

        $this->userTutorialRepository->shouldReceive('save')
            ->never();

        request::setMockVars([
            'currentstep' => 27,
            'isforward' => 1
        ]);

        $this->subject->handle($this->game);
    }

    /** @param ArrayCollection<int, UserTutorial> $tutorials */
    private function expectCommonCalls(TutorialStep $currentStep, ArrayCollection $tutorials): void
    {
        $this->game->shouldReceive('setView')
            ->with(Noop::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->user->shouldReceive('getTutorials')
            ->withNoArgs()
            ->once()
            ->andReturn($tutorials);

        $this->tutorialStepRepository->shouldReceive('find')
            ->with(27)
            ->once()
            ->andReturn($currentStep);
    }

    private function mockTutorialStep(int $id): MockInterface&TutorialStep
    {
        $step = $this->mock(TutorialStep::class);
        $step->shouldReceive('getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($id);

        return $step;
    }
}
