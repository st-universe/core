<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\FinishTutorial;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;
use Stu\Module\Game\View\Noop\Noop;

final class FinishTutorial implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FINISH_TUTORIAL';

    public function __construct(private UserTutorialRepositoryInterface $userTutorial) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {


        $view = request::postString('module');
        if ($view != null) {
            $this->userTutorial->truncateByUserAndModule($game->getUser(), $view);
        }

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
