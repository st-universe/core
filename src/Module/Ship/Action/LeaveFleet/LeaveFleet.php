<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveFleet;

use AccessViolation;
use Ship;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class LeaveFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_FLEET';

    private $leaveFleetRequest;

    public function __construct(
        LeaveFleetRequestInterface $leaveFleetRequest
    ) {
        $this->leaveFleetRequest = $leaveFleetRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        /**
         * @var Ship $ship
         */
        $ship = Ship::getById($this->leaveFleetRequest->getShipId());
        if (!$ship->ownedByCurrentUser()) {
            throw new AccessViolation();
        }

        if (!$ship->isInFleet()) {
            return;
        }
        if ($ship->isFleetLeader()) {
            return;
        }
        $ship->leaveFleet();
        $ship->save();

        $game->addInformation(
            sprintf(_('Die %s hat die Flotte verlassen'), $ship->getName())
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
