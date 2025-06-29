<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\JoinFleet;

use request;
use Stu\Component\Game\GameEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\FleetRepositoryInterface;

abstract class AbstractJoinFleet
{
    protected LoggerUtilInterface $loggerUtil;

    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        protected ShipLoaderInterface $shipLoader,
        private InteractionCheckerInterface $interactionChecker,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function tryToAddToFleet(Ship $ship, GameControllerInterface $game): void
    {
        $fleetId = request::indInt('fleetid');
        $fleet = $this->fleetRepository->find($fleetId);

        if ($fleet === null || $fleet->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolationException();
        }

        if ($ship->getFleet() !== null) {
            $game->addInformationf(_('%s: Das Schiff ist bereits in einer Flotte.'), $ship->getName());
            return;
        }

        if ($ship->getTakeoverPassive() !== null) {
            $game->addInformationf(_('%s: Schiffsübernahme verhindert den Beitritt.'), $ship->getName());
            return;
        }

        if ($this->isTholianWebPreventing($fleet->getLeadShip(), $ship)) {
            $game->addInformationf(_('%s: Ein Energienetz verhindert den Beitritt.'), $ship->getName());
            return;
        }

        if ($ship->isStation()) {
            return;
        }

        if ($fleet->getLeadShip()->getId() === $ship->getId()) {
            return;
        }
        if (!$this->interactionChecker->checkPosition($fleet->getLeadShip(), $ship)) {
            return;
        }
        if ($ship->isTractored()) {
            $game->addInformationf(
                _('%s: Aktion nicht möglich, da Schiff von einem Traktorstrahl gehalten wird.'),
                $ship->getName(),
            );
            return;
        }
        $newCrewAmount = $fleet->getCrewSum() + ($ship->getBuildplan()?->getCrew() ?? 0);
        if ($newCrewAmount > GameEnum::CREW_PER_FLEET) {
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

    private function isTholianWebPreventing(Ship $fleetLeader, Ship $ship): bool
    {
        $fleetLeaderWeb = $fleetLeader->getHoldingWeb();
        $shipWeb = $ship->getHoldingWeb();

        if ($fleetLeaderWeb === $shipWeb) {
            return false;
        }

        if ($fleetLeaderWeb !== null && $fleetLeaderWeb->isFinished()) {
            return true;
        }
        return $shipWeb !== null && $shipWeb->isFinished();
    }
}
