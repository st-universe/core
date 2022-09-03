<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\JoinFleet;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class JoinFleetFromNbs extends AbstractJoinFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_JOIN_FLEET_NBS';

    public function handle(GameControllerInterface $game): void
    {
        $this->tryToAddToFleet($game);

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
