<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateWarp;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class DeactivateWarp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_WARP';

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
        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $success = $this->helper->deactivate(
            $wrapper,
            ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
            $game
        );

        if ($success) {
            $ship = $wrapper->get();
            $traktoredShip = $ship->getTractoredShip();

            //Alarm-Rot check for ship
            $this->alertRedHelper->doItAll($ship, $game);

            //Alarm-Rot check for traktor ship
            if ($traktoredShip !== null) {
                $this->alertRedHelper->doItAll($traktoredShip, $game, $ship);
            }

            if ($ship->isDestroyed()) {
                return;
            }
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
