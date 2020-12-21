<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetAlertYellow;

use request;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class FleetAlertYellow implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ALERT_YELLOW';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

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
            try {
                $ship->setAlertState(ShipAlertStateEnum::ALERT_YELLOW);
            } catch (ShipSystemException $e) {
                $game->addInformation(sprintf(_('%s: Nicht genügend Energie um auf Alarm-Gelb zu wechseln'), $ship->getName()));
                continue;
            }

            $this->shipRepository->save($ship);
        }
        $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe Gelb'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
