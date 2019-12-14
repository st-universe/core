<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeleteFleet;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class DeleteFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_FLEET';

    private DeleteFleetRequestInterface $deleteFleetRequest;

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        DeleteFleetRequestInterface $deleteFleetRequest,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->deleteFleetRequest = $deleteFleetRequest;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipRepository->find($this->deleteFleetRequest->getShipId());
        if ($ship === null || $ship->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }
        if (!$ship->getFleetId()) {
            return;
        }
        if (!$ship->isFleetLeader()) {
            return;
        }

        $fleet = $ship->getFleet();

        foreach ($fleet->getShips() as $fleetShip) {
            $fleetShip->setFleet(null);

            $this->shipRepository->save($fleetShip);
        }

        $this->fleetRepository->delete($fleet);

        $game->addInformation(_('Die Flotte wurde aufgelöst'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
