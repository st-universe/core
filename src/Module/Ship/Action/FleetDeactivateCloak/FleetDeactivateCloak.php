<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetDeactivateCloak;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class FleetDeactivateCloak implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_DEACTIVATE_CLOAK';

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
        $this->helper->deactivateFleet(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_CLOAK, $game);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        //Alarm-Rot check
        $this->alertRedHelper->doItAll($ship, $game);

        if ($ship->isDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
