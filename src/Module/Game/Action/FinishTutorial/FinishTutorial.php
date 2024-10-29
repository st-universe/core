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

    public function __construct(private UserTutorialRepositoryInterface $userTutorial, private TutorialStepRepositoryInterface $tutorialStepRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {

        $stepId = request::postIntFatal('stepId');
        $tutorial = $this->tutorialStepRepository->findOneBy(
            ['id' => $stepId]
        );
        if ($tutorial == null) {
            throw new RuntimeException('Current Tutorial not found');
        }

        $view = $tutorial->getView();
        if ($view == null) {
            throw new RuntimeException('view not found');
        }

        $userTutorial = $this->userTutorial->findUserTutorialByUserAndView(
            $game->getUser(),
            $view
        );

        if ($userTutorial != null) {
            $this->userTutorial->delete($userTutorial);
        }

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}