<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\ToggleBatteryReload;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class ToggleBatteryReload implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TOGGLE_BATT_RELOAD';
    public function __construct(private ShipLoaderInterface $shipLoader)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $wrapper = $this->shipLoader->getWrapperByIdAndUser(request::getIntFatal('id'), $game->getUser()->getId());

        if (!$wrapper->get()->isBase()) {
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        $epsSystem->setReloadBattery(!$epsSystem->reloadBattery())->update();

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $game->addInformationf('Die automatische Ladung der Ersatzbatterie ist nun %s', $epsSystem->reloadBattery() ? 'aktiv' : 'inaktiv');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
