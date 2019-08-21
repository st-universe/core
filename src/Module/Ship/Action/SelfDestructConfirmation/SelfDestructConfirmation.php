<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SelfDestructConfirmation;

use Stu\Control\ActionControllerInterface;
use Stu\Control\GameController;
use Stu\Control\GameControllerInterface;

final class SelfDestructConfirmation implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_SHOW_SELFDESTRUCT';

    public function handle(GameControllerInterface $game): void
    {
        $game->addInformation(_('Die Selbstzerstörung wurde durchgeführt'));

        $game->setView(GameController::DEFAULT_VIEW);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
