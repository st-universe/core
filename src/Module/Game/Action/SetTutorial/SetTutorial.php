<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\SetTutorial;

use Override;
use request;
use RuntimeException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\View\Noop\Noop;
use Stu\Orm\Repository\TutorialStepRepositoryInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;

final class SetTutorial implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_TUTORIAL';

    public function __construct(
        private TutorialStepRepositoryInterface $tutorialStepRepository,
        private UserTutorialRepositoryInterface $userTutorialRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Noop::VIEW_IDENTIFIER);
        $nextTutorial = null;

        $currentStepId = request::postIntFatal('currentstep');
        $direction = request::postStringFatal('direction');


        $tutorial = $this->tutorialStepRepository->findOneBy(
            ['id' => $currentStepId]
        );
        if ($tutorial == null) {
            throw new RuntimeException('Current Tutorial not found');
        }

        $view = $tutorial->getView();
        if ($view == null) {
            throw new RuntimeException('view not found');
        }

        $userTutorial = $this->userTutorialRepository->findUserTutorialByUserAndView(
            $game->getUser(),
            $view
        );
        if ($userTutorial == null) {
            throw new RuntimeException('UserTutorial not found');
        }

        $currentTutorial = $userTutorial->getTutorialStep();
        if ($currentTutorial == null) {
            throw new RuntimeException('currentTutorial not found');
        }

        $sort = $currentTutorial->getSort();
        if ($sort == null) {
            throw new RuntimeException('sort not found');
        }

        if ($direction == 'forward') {
            $nextTutorial = $this->tutorialStepRepository->findByViewContextAndSort($view, $sort + 1);
        }
        if ($direction == 'back') {
            $nextTutorial = $this->tutorialStepRepository->findByViewContextAndSort($view, $sort - 1);
        }


        if ($nextTutorial == null) {
            throw new RuntimeException(sprintf('Tutorial not found for view %s and sort %d', $view, $sort));
        }

        if ($userTutorial == null) {
            throw new RuntimeException('this should not happen');
        }
        $userTutorial->setTutorialStep($nextTutorial);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}