<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\JoinFleet;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\View\ShowInformation\ShowInformation;

final class JoinFleetInShiplist extends AbstractJoinFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_JOIN_FLEET';

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $chosenShipIds = request::postArray('chosen');

        foreach ($chosenShipIds as $shipId) {
            $ship = $this->shipLoader->getByIdAndUser((int)$shipId, $game->getUser()->getId());
            $this->tryToAddToFleet($ship, $game);
        }

        $game->setView(ShowInformation::VIEW_IDENTIFIER);
        $game->addExecuteJS(sprintf('refreshShiplistFleet(%d);', request::postIntFatal('fleetid')));
        $game->addExecuteJS('refreshShiplistSingles();');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
