<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetAlertRed;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class FleetAlertRed implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ALERT_RED';

    private $shipLoader;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        foreach ($ship->getFleet()->getShips() as $key => $ship) {
            $ship->setAlertState(ALERT_RED);

            $this->shipRepository->save($ship);
        }
        $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe Rot'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
