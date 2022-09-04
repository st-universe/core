<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\JoinFleet;

use request;
use Stu\Exception\AccessViolation;
use Stu\Component\Game\GameEnum;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

abstract class AbstractJoinFleet
{
    private FleetRepositoryInterface $fleetRepository;

    protected ShipLoaderInterface $shipLoader;

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

    public function tryToAddToFleet(ShipInterface $ship, GameControllerInterface $game): void
    {
        $fleetId = request::getIntFatal('fleetid');
        $fleet = $this->fleetRepository->find($fleetId);

        if ($fleet === null || $fleet->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        if ($ship->getFleet() !== null) {
            $game->addInformationf(_('%s: Das Schiff ist bereits in einer Flotte.', $ship->getName()));
            return;
        }

        if ($fleet->getLeadShip()->getId() === $ship->getId()) {
            return;
        }
        if (!$this->positionChecker->checkPosition($fleet->getLeadShip(), $ship)) {
            return;
        }
        if ($ship->isTractored()) {
            $game->addInformationf(
                _('%s: Aktion nicht möglich, da Schiff von einem Traktorstrahl gehalten wird.', $ship->getName()),
            );
            return;
        }
        if ($fleet->getCrewSum() + $ship->getBuildplan()->getCrew() > GameEnum::CREW_PER_FLEET) {
            $game->addInformation(sprintf(
                _('%s: Es sind maximal %d Crew pro Flotte möglich'),
                $ship->getName(),
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
}
