<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Shutdown;

use request;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class Shutdown implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SHUTDOWN';

    private ActivatorDeactivatorHelperInterface $helper;

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ActivatorDeactivatorHelperInterface $helper,
        ShipLoaderInterface $shipLoader
    ) {
        $this->helper = $helper;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $ship = $this->shipLoader->getByIdAndUser(request::indInt('id'), $game->getUser()->getId());

        //deactivate all systems except life support and troop quarters
        foreach ($ship->getActiveSystems() as $system) {
            if (
                $system->getSystemType() != ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT &&
                $system->getSystemType() != ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS
            ) {
                $this->helper->deactivate(request::indInt('id'), $system->getSystemType(), $game);
            }
        }

        //set alert to green
        $ship->setAlertStateGreen();

        $game->addInformation(_("Der Energieverbrauch wurde auf ein Minimum reduziert"));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
