<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\JoinFleet;

use AccessViolation;
use Fleet;
use Ship;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class JoinFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_JOIN_FLEET';

    private $joinFleetRequest;

    public function __construct(
        JoinFleetRequestInterface $joinFleetRequest
    ) {
        $this->joinFleetRequest = $joinFleetRequest;
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

        // @todo zusätzliche checks für flotten
        $fleet = Fleet::getUserFleetById($this->joinFleetRequest->getFleetId(), $game->getUser()->getId());
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
