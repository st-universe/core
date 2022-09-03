<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\JoinFleet;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class JoinFleetInShiplist extends AbstractJoinFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_JOIN_FLEET';

    public function handle(GameControllerInterface $game): void
    {
        $fleet = $this->tryToAddToFleet($game);

        $game->setTemplateVar('fleet', $fleet);
        $game->showMacro('html/shipmacros.xhtml/shiplist_fleetform');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
