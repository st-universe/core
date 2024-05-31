<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Shutdown;

use request;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class Shutdown implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SHUTDOWN';

    private ActivatorDeactivatorHelperInterface $helper;

    private ShipLoaderInterface $shipLoader;

    private AlertRedHelperInterface $alertRedHelper;

    private ShipSystemManagerInterface $shipSystemManager;

    public function __construct(
        ActivatorDeactivatorHelperInterface $helper,
        ShipLoaderInterface $shipLoader,
        AlertRedHelperInterface $alertRedHelper,
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->helper = $helper;
        $this->shipLoader = $shipLoader;
        $this->alertRedHelper = $alertRedHelper;
        $this->shipSystemManager = $shipSystemManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipLoader->getByIdAndUser(request::indInt('id'), $game->getUser()->getId());

        $traktoredShip = $ship->getTractoredShip();

        $triggerAlertRed = $ship->getWarpDriveState() || $ship->getCloakState();

        //deactivate all systems except life support and troop quarters
        foreach ($this->shipSystemManager->getActiveSystems($ship) as $system) {
            if (
                $system->getSystemType() !== ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT &&
                $system->getSystemType() !== ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS
            ) {
                $this->helper->deactivate(request::indInt('id'), $system->getSystemType(), $game);
            }
        }

        //set alert to green
        $ship->setAlertStateGreen();

        $game->addInformation(_("Der Energieverbrauch wurde auf ein Minimum reduziert"));

        if ($triggerAlertRed) {
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
