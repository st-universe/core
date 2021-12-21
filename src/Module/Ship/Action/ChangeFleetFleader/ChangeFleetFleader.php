<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ChangeFleetFleader;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class ChangeFleetFleader implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_LEADER';

    private FleetRepositoryInterface $fleetRepository;

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        ShipLoaderInterface $shipLoader
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $shipId = request::indInt('id');
        $targetId = request::getIntFatal('target');

        $shipArray = $this->shipLoader->getByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $ship = $shipArray[$shipId];
        $target = $shipArray[$targetId];

        if ($ship->getFleetId() === null) {
            return;
        }

        if ($target === null) {
            return;
        }

        if ($target->getUser() !== $ship->getUser()) {
            return;
        }

        $fleet = $ship->getFleet();
        $fleet->setLeadShip($target);

        $this->fleetRepository->save($fleet);

        $ship->setIsFleetLeader(false);
        $target->setIsFleetLeader(true);

        $this->shipLoader->save($ship);
        $this->shipLoader->save($target);

        $game->addInformation(sprintf(_('Die %s führt nun die Flotte an'), $target->getName()));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
