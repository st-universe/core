<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\CreateFleet;

use AccessViolation;
use FleetData;
use Ship;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class CreateFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_NEW_FLEET';

    private $createFleetRequest;

    public function __construct(
        CreateFleetRequestInterface $createFleetRequest
    ) {
        $this->createFleetRequest = $createFleetRequest;
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
        $fleet = new FleetData();
        $fleet->setFleetLeader($ship->getId());
        $fleet->setUserId($game->getUser()->getId());
        $fleet->save();

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
