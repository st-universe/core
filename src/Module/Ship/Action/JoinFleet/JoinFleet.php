<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\JoinFleet;

use AccessViolation;
use Ship;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class JoinFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_JOIN_FLEET';

    private $joinFleetRequest;

    private $fleetRepository;

    public function __construct(
        JoinFleetRequestInterface $joinFleetRequest,
        FleetRepositoryInterface $fleetRepository
    ) {
        $this->joinFleetRequest = $joinFleetRequest;
        $this->fleetRepository = $fleetRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        /**
         * @var Ship $ship
         */
        $ship = Ship::getById($this->joinFleetRequest->getShipId());
        if (!$ship->ownedByCurrentUser()) {
            throw new AccessViolation();
        }

        $fleet = $this->fleetRepository->find($this->joinFleetRequest->getFleetId());

        if ($fleet === null || $fleet->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        if ($fleet->getFleetLeader() == $ship->getId()) {
            return;
        }
        if (!checkPosition($fleet->getLeadShip(), $ship)) {
            return;
        }
        if ($fleet->getPointSum() + $ship->getRump()->getShipRumpCategory()->getPoints() > POINTS_PER_FLEET) {
            $game->addInformation(sprintf(_('Es sind maximal %d Schiffspunkte pro Flotte möglich'), POINTS_PER_FLEET));
            return;
        }
        $ship->setFleetId($fleet->getId());
        $ship->save();

        $game->addInformation(sprintf(
            _('Die %s ist der Flotte %s beigetreten'),
            $ship->getName(),
            $fleet->getName()
        ));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
