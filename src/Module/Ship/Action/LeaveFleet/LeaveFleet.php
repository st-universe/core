<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveFleet;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class LeaveFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_FLEET';

    private LeaveFleetRequestInterface $leaveFleetRequest;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        LeaveFleetRequestInterface $leaveFleetRequest,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->leaveFleetRequest = $leaveFleetRequest;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipRepository->find($this->leaveFleetRequest->getShipId());
        if ($ship === null || $ship->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        if (!$ship->getFleetId()) {
            return;
        }
        if ($ship->isFleetLeader()) {
            return;
        }
        $ship->setFleet(null);

        $this->shipRepository->save($ship);

        $game->addInformation(
            sprintf(_('Die %s hat die Flotte verlassen'), $ship->getName())
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
