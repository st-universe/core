<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\JoinFleet;

use Stu\Exception\AccessViolation;
use Stu\Component\Game\GameEnum;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class JoinFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_JOIN_FLEET';

    private JoinFleetRequestInterface $joinFleetRequest;

    private FleetRepositoryInterface $fleetRepository;

    private ShipLoaderInterface $shipLoader;

    private PositionCheckerInterface $positionChecker;

    public function __construct(
        JoinFleetRequestInterface $joinFleetRequest,
        FleetRepositoryInterface $fleetRepository,
        ShipLoaderInterface $shipLoader,
        PositionCheckerInterface $positionChecker
    ) {
        $this->joinFleetRequest = $joinFleetRequest;
        $this->fleetRepository = $fleetRepository;
        $this->shipLoader = $shipLoader;
        $this->positionChecker = $positionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipLoader->getByIdAndUser($this->joinFleetRequest->getShipId(), $game->getUser()->getId());

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
        if ($ship->traktorBeamToShip()) {
            $game->addInformation(
                _('Aktion nicht möglich, da Schiff von einem Traktorstrahl gehalten wird.'),
            );
            return;
        }
        if ($fleet->getCrewSum() + $ship->getBuildplan()->getCrew() > GameEnum::CREW_PER_FLEET) {
            $game->addInformation(sprintf(
                _('Es sind maximal %d Crew pro Flotte möglich'),
                GameEnum::CREW_PER_FLEET
            ));
            return;
        }
        $ship->setFleet($fleet);

        $fleet->getShips()->add($ship);

        $this->shipLoader->save($ship);

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
