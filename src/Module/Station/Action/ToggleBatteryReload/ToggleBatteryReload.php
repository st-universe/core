<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\ToggleBatteryReload;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class ToggleBatteryReload implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TOGGLE_BATT_RELOAD';
    public function __construct(private StationLoaderInterface $stationLoader) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $wrapper = $this->stationLoader->getWrapperByIdAndUser(request::getIntFatal('id'), $game->getUser()->getId());

        if (!$wrapper->get()->isStation()) {
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        $epsSystem->setReloadBattery(!$epsSystem->reloadBattery())->update();

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $game->addInformationf('Die automatische Ladung der Ersatzbatterie ist nun %s', $epsSystem->reloadBattery() ? 'aktiv' : 'inaktiv');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
