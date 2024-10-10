<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\SetTutorial;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;
use Stu\Module\Game\View\Noop\Noop;

final class SetTutorial implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_TUTORIAL';

    public function __construct(private UserTutorialRepositoryInterface $userTutorial) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {

        $nextstep = request::postInt('nextstep');

        $view = request::postString('module');
        if ($view != null) {
            $tutorial = $this->userTutorial->findByUserAndModule($game->getUser(), $view);
            if ($tutorial !== null) {

                $tutorial->setStep($nextstep);


                $this->userTutorial->save($tutorial);
            }
        }

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
