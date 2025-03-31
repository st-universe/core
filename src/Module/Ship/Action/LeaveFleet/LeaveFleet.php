<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveFleet;

use Override;
use Stu\Exception\EntityLockedException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowInformation\ShowInformation;
use Stu\Orm\Entity\ShipInterface;

final class LeaveFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_LEAVE_FLEET';

    public function __construct(
        private LeaveFleetRequestInterface $leaveFleetRequest,
        private ShipLoaderInterface $shipLoader
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowInformation::VIEW_IDENTIFIER);

        try {
            $ship = $this->shipLoader->getByIdAndUser(
                $this->leaveFleetRequest->getShipId(),
                $game->getUser()->getId()
            );

            $this->entferneSchiffAusFlotte($ship, $game);
        } catch (EntityLockedException $e) {
            $game->addInformation($e->getMessage());
        }
    }

    private function entferneSchiffAusFlotte(ShipInterface $ship, GameControllerInterface $game): void
    {
        $fleet = $ship->getFleet();
        if ($fleet === null) {
            return;
        }
        if ($ship->isFleetLeader()) {
            return;
        }

        $game->addExecuteJS(sprintf('refreshShiplistFleet(%d);', $ship->getFleetId()));
        $game->addExecuteJS('refreshShiplistSingles();');

        $fleet->getShips()->removeElement($ship);
        $ship->setFleet(null);

        $this->shipLoader->save($ship);

        $game->addInformation(
            sprintf(_('Die %s hat die Flotte verlassen'), $ship->getName())
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
