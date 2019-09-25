<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\CreateFleet;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class CreateFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_NEW_FLEET';

    private $createFleetRequest;

    private $fleetRepository;

    private $shipRepository;

    public function __construct(
        CreateFleetRequestInterface $createFleetRequest,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->createFleetRequest = $createFleetRequest;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipRepository->find($this->createFleetRequest->getShipId());
        if ($ship === null || $ship->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }
        if ($ship->getFleetId()) {
            return;
        }
        if ($ship->isBase()) {
            return;
        }

        $fleet = $this->fleetRepository->prototype();
        $fleet->setLeadShip($ship);
        $fleet->setUser($game->getUser());
        $fleet->setName(_('Flotte'));

        $this->fleetRepository->save($fleet);

        $ship->setFleet($fleet);

        $this->shipRepository->save($ship);

        $game->addInformation(_('Die Flotte wurde erstellt'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
