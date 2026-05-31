<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\SetTutorial;

use request;
use RuntimeException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\View\Noop\Noop;
use Stu\Orm\Entity\TutorialStep;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserTutorial;
use Stu\Orm\Repository\TutorialStepRepositoryInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;

final class SetTutorial implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_TUTORIAL';

    public function __construct(
        private TutorialStepRepositoryInterface $tutorialStepRepository,
        private UserTutorialRepositoryInterface $userTutorialRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Noop::VIEW_IDENTIFIER);

        $currentStepId = request::postIntFatal('currentstep');
        $isForward = request::postIntFatal('isforward') !== 0;

        $tutorialStep = $this->tutorialStepRepository->find($currentStepId);
        if ($tutorialStep == null) {
            throw new RuntimeException('Current Tutorial not found');
        }

        $userTutorial = $this->determineUserTutorial($tutorialStep, $isForward, $game->getUser());

        $followingTutorial = $isForward ? $tutorialStep->getNextStep() : $tutorialStep->getPreviousStep();
        if ($followingTutorial == null) {
            throw new RuntimeException(sprintf(
                'Tutorial not found for currentStepId %d and isForward %d',
                $currentStepId,
                $isForward ? 1 : 0
            ));
        }

        if ($userTutorial == null) {
            return;
        }

        $userTutorial->setTutorialStep($followingTutorial);
        $this->userTutorialRepository->save($userTutorial);
    }

    private function determineUserTutorial(TutorialStep $tutorialStep, bool $isForward, User $user): ?UserTutorial
    {
        $step = $tutorialStep;
        $tutorials = $user->getTutorials();

        do {
            $userTutorial = $tutorials->get($step->getId());
            if ($userTutorial !== null) {
                return $userTutorial;
            }

            $step = $isForward ? $step->getPreviousStep() : $step->getNextStep();
        } while ($step !== null);

        return null;
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
