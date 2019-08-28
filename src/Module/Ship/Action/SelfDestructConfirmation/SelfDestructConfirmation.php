<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SelfDestructConfirmation;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;

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
