<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\JoinFleet;

use Stu\Exception\AccessViolation;
use Stu\Component\Game\GameEnum;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class JoinFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_JOIN_FLEET';

    private JoinFleetRequestInterface $joinFleetRequest;

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private PositionCheckerInterface $positionChecker;

    public function __construct(
        JoinFleetRequestInterface $joinFleetRequest,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        PositionCheckerInterface $positionChecker
    ) {
        $this->joinFleetRequest = $joinFleetRequest;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->positionChecker = $positionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipRepository->find($this->joinFleetRequest->getShipId());
        if ($ship === null || $ship->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $fleet = $this->fleetRepository->find($this->joinFleetRequest->getFleetId());

        if ($fleet === null || $fleet->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        if ($fleet->getLeadShip()->getId() === $ship->getId()) {
            return;
        }
        if (!$this->positionChecker->checkPosition($fleet->getLeadShip(), $ship)) {
            return;
        }
        if ($fleet->getPointSum() + $ship->getRump()->getShipRumpCategory()->getPoints() > GameEnum::POINTS_PER_FLEET) {
            $game->addInformation(sprintf(_('Es sind maximal %d Schiffspunkte pro Flotte möglich'),
                GameEnum::POINTS_PER_FLEET));
            return;
        }
        $ship->setFleet($fleet);

        $fleet->getShips()->add($ship);

        $this->shipRepository->save($ship);

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
