<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DisplayNotOwner;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;

final class DisplayNotOwner implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_NOT_OWNER';

    public function handle(GameControllerInterface $game): void
    {
        $game->addInformation(_('Du bist nicht Besitzer dieses Schiffes'));

        $game->setView(GameController::DEFAULT_VIEW);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
