<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ChangeFleetFixation;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class ChangeFleetFixation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_FLEET_FIXATION';

    public function __construct(private FleetRepositoryInterface $fleetRepository, private ShipLoaderInterface $shipLoader)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $fleet = $ship->getFleet();
        if ($fleet === null) {
            return;
        }
        if (!$ship->isFleetLeader()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        if (request::postString('fleetfixed') !== false) {
            $fleet->setIsFleetFixed(true);
            $game->addInformation(_('Die Flotte ist nun fixiert'));
        } else {
            $fleet->setIsFleetFixed(false);
            $game->addInformation(_('Die Flotte ist nun nicht mehr fixiert'));
        }

        $this->fleetRepository->save($fleet);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
