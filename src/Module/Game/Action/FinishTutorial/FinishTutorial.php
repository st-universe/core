<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\FinishTutorial;

use Override;
use request;
use RuntimeException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;
use Stu\Orm\Repository\TutorialStepRepositoryInterface;
use Stu\Module\Game\View\Noop\Noop;

final class FinishTutorial implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FINISH_TUTORIAL';

    public function __construct(
        private UserTutorialRepositoryInterface $userTutorialRepository,
        private TutorialStepRepositoryInterface $tutorialStepRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Noop::VIEW_IDENTIFIER);

        $tutorialStep = $this->tutorialStepRepository->find(request::postIntFatal('stepId'));
        if ($tutorialStep === null) {
            throw new RuntimeException('Current Tutorial not found');
        }

        $user = $game->getUser();

        $allStepsForView = $this->tutorialStepRepository->findBy([
            'module' => $tutorialStep->getModule(),
            'view' => $tutorialStep->getView()
        ]);

        if (empty($allStepsForView)) {
            return;
        }

        $userTutorials = $user->getTutorials();

        foreach ($allStepsForView as $step) {
            $userTutorial = $userTutorials->get($step->getId());
            if ($userTutorial !== null) {
                $this->userTutorialRepository->delete($userTutorial);
            }
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
