<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveFleet;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class LeaveFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_FLEET';

    private LeaveFleetRequestInterface $leaveFleetRequest;

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        LeaveFleetRequestInterface $leaveFleetRequest,
        ShipLoaderInterface $shipLoader
    ) {
        $this->leaveFleetRequest = $leaveFleetRequest;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipLoader->getByIdAndUser($this->leaveFleetRequest->getShipId(), $game->getUser()->getId());

        if (!$ship->getFleetId()) {
            return;
        }
        if ($ship->isFleetLeader()) {
            return;
        }
        $ship->setFleet(null);

        $this->shipLoader->save($ship);

        $game->addInformation(
            sprintf(_('Die %s hat die Flotte verlassen'), $ship->getName())
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
