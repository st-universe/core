<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\ToggleBatteryReload;

use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Station\Lib\StationLoaderInterface;

final class ToggleBatteryReload implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TOGGLE_BATT_RELOAD';
    public function __construct(private StationLoaderInterface $stationLoader) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $wrapper = $this->stationLoader->getWrapperByIdAndUser(request::getIntFatal('id'), $context->getUser()->getId());

        if (!$wrapper->get()->isStation()) {
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null) {
            throw new SanityCheckException(sprintf('stationId %d does not have eps system', request::getIntFatal('id')));
        }
        $epsSystem->setReloadBattery(!$epsSystem->reloadBattery())->update();

        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $context->getInfo()->addInformationf('Die automatische Ladung der Ersatzbatterie ist nun %s', $epsSystem->reloadBattery() ? 'aktiv' : 'inaktiv');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
