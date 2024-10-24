<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\SetTutorial;

use Override;
use request;
use RuntimeException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;
use Stu\Module\Game\View\Noop\Noop;
use Stu\Orm\Entity\UserTutorialInterface;
use Stu\Orm\Repository\TutorialStepRepositoryInterface;

final class SetTutorial implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_TUTORIAL';

    public function __construct(
        private UserTutorialRepositoryInterface $userTutorialRepository,
        private TutorialStepRepositoryInterface $tutorialStepRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Noop::VIEW_IDENTIFIER);

        $currentStepId = request::postIntFatal('currentstep');
        $isForward = request::postIntFatal('isforward');

        /** @var ?UserTutorialInterface */
        $userTutorial = $game->getUser()->getTutorials()->get($currentStepId);
        if ($userTutorial === null) {
            throw new RuntimeException('this should not happen');
        }

        if ($isForward) {
            $this->setNextSteps($userTutorial);
        } else {
            $this->setPreviousStep($userTutorial);
        }
    }

    private function setNextSteps(UserTutorialInterface $userTutorial): void
    {
        $user = $userTutorial->getUser();
        $tutorialStep = $userTutorial->getTutorialStep();
        $nextStepIds = $tutorialStep->getNextStepIds();

        $this->userTutorialRepository->delete($userTutorial);

        if ($nextStepIds == []) {
            return;
        }

        foreach ($nextStepIds as $stepId) {
            $step = $this->tutorialStepRepository->find($stepId);
            if ($step === null) {
                throw new RuntimeException(sprintf('no tutorialStep with id %d present', $stepId));
            }

            $userTutorial = $this->userTutorialRepository->prototype();
            $userTutorial->setUser($user);
            $userTutorial->setTutorialStep($step);
            $this->userTutorialRepository->save($userTutorial);
        }
    }

    private function setPreviousStep(UserTutorialInterface $userTutorial): void
    {

        $tutorialStep = $userTutorial->getTutorialStep();
        $previousStep = $tutorialStep->getPreviousStep();
        if ($previousStep === null) {
            return;
        }

        $userTutorial->setTutorialStep($previousStep);
        $this->userTutorialRepository->save($userTutorial);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
