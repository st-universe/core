<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ChangeFleetFleader;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
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

        $shipArray = $this->shipLoader->getWrappersByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $shipArray[$shipId];
        $ship = $wrapper->get();

        if ($ship->getFleetId() === null) {
            return;
        }

        $targetWrapper = $shipArray[$targetId];
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if ($target->getUser() !== $ship->getUser()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);

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
