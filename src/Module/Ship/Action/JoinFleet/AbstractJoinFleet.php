<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\JoinFleet;

use request;
use Stu\Exception\AccessViolation;
use Stu\Component\Game\GameEnum;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

abstract class AbstractJoinFleet
{
    private FleetRepositoryInterface $fleetRepository;

    private ShipLoaderInterface $shipLoader;

    private PositionCheckerInterface $positionChecker;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        ShipLoaderInterface $shipLoader,
        PositionCheckerInterface $positionChecker
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->shipLoader = $shipLoader;
        $this->positionChecker = $positionChecker;
    }

    public function tryToAddToFleet(GameControllerInterface $game): FleetInterface
    {
        $shipId = request::indInt('id');
        $fleetId = request::indInt('fleetid');

        $ship = $this->shipLoader->getByIdAndUser($shipId, $game->getUser()->getId());
        $fleet = $this->fleetRepository->find($fleetId);

        if ($fleet === null || $fleet->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        if ($fleet->getLeadShip()->getId() === $ship->getId()) {
            return $fleet;
        }
        if (!$this->positionChecker->checkPosition($fleet->getLeadShip(), $ship)) {
            return $fleet;
        }
        if ($ship->isTractored()) {
            $game->addInformation(
                _('Aktion nicht möglich, da Schiff von einem Traktorstrahl gehalten wird.'),
            );
            return $fleet;
        }
        if ($fleet->getCrewSum() + $ship->getBuildplan()->getCrew() > GameEnum::CREW_PER_FLEET) {
            $game->addInformation(sprintf(
                _('Es sind maximal %d Crew pro Flotte möglich'),
                GameEnum::CREW_PER_FLEET
            ));
            return $fleet;
        }
        $ship->setFleet($fleet);

        $fleet->getShips()->add($ship);

        $this->shipLoader->save($ship);

        $game->addInformation(sprintf(
            _('Die %s ist der Flotte %s beigetreten'),
            $ship->getName(),
            $fleet->getName()
        ));

        return $fleet;
    }
}
