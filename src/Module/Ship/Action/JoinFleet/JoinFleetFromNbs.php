<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\JoinFleet;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class JoinFleetFromNbs extends AbstractJoinFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_JOIN_FLEET_NBS';

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $shipId = request::getIntFatal('id');
        $ship = $this->shipLoader->getByIdAndUser($shipId, $game->getUser()->getId());

        $this->tryToAddToFleet($ship, $game);

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
