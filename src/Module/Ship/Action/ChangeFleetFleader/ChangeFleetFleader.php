<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ChangeFleetFleader;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class ChangeFleetFleader implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FLEET_LEADER';

    public function __construct(private FleetRepositoryInterface $fleetRepository, private ShipLoaderInterface $shipLoader)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $shipId = request::indInt('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        if ($ship->getFleetId() === null) {
            return;
        }

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if ($target->getUser() !== $ship->getUser()) {
            return;
        }

        $fleet = $ship->getFleet();
        if ($fleet === null) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $fleet->setLeadShip($target);
        $this->fleetRepository->save($fleet);

        $ship->setIsFleetLeader(false);
        $target->setIsFleetLeader(true);

        $this->shipLoader->save($ship);
        $this->shipLoader->save($target);

        $game->addInformation(sprintf(_('Die %s führt nun die Flotte an'), $target->getName()));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
