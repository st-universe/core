<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\CreateFleet;

use AccessViolation;
use Ship;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class CreateFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_NEW_FLEET';

    private $createFleetRequest;

    private $fleetRepository;

    public function __construct(
        CreateFleetRequestInterface $createFleetRequest,
        FleetRepositoryInterface $fleetRepository
    ) {
        $this->createFleetRequest = $createFleetRequest;
        $this->fleetRepository = $fleetRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        /**
         * @var Ship $ship
         */
        $ship = Ship::getById($this->createFleetRequest->getShipId());
        if (!$ship->ownedByCurrentUser()) {
            throw new AccessViolation();
        }
        if ($ship->isInFleet()) {
            return;
        }
        if ($ship->isBase()) {
            return;
        }

        $fleet = $this->fleetRepository->prototype();
        $fleet->setFleetLeader($ship->getId());
        $fleet->setUser($game->getUser());

        $this->fleetRepository->save($fleet);

        $ship->setFleetId($fleet->getId());
        $ship->setFleet($fleet);
        $ship->save();

        $game->addInformation(_('Die Flotte wurde erstellt'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
