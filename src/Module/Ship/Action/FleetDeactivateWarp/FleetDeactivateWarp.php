<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetDeactivateWarp;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;

final class FleetDeactivateWarp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_DEACTIVATE_WARP';

    private ActivatorDeactivatorHelperInterface $helper;

    private AlertRedHelperInterface $alertRedHelper;

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ActivatorDeactivatorHelperInterface $helper,
        ShipLoaderInterface $shipLoader,
        AlertRedHelperInterface $alertRedHelper
    ) {
        $this->helper = $helper;
        $this->alertRedHelper = $alertRedHelper;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->helper->deactivateFleet(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_WARPDRIVE, $game);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $tractoredShips = $this->getTractoredShips($ship);

        //Alarm-Rot check for fleet
        $this->alertRedHelper->doItAll($ship, $game);

        //Alarm-Rot check for tractored ships
        foreach ($tractoredShips as [$tractoringShip, $tractoredShip]) {
            $this->alertRedHelper->doItAll($tractoredShip, $game, $tractoringShip);
        }

        if ($ship->isDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    private function getTractoredShips(ShipInterface $ship): array
    {
        $result = [];

        foreach ($ship->getFleet()->getShips() as $fleetShip) {
            if ($fleetShip->isTractoring()) {
                $result[] = [$fleetShip, $fleetShip->getTractoredShip()];
            }
        }

        return $result;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
