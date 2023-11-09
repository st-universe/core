<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetWarpSplit;

use request;
use RuntimeException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class FleetWarpSplit implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_WARP_SPLIT';

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            false
        );

        $warpdrive = $wrapper->getWarpDriveSystemData();
        if ($warpdrive === null) {
            throw new RuntimeException('no warpdrive in fleet leader');
        }
        $warpsplit = $warpdrive->getWarpdriveSplit();

        $fleetWrapper = $wrapper->getFleetWrapper();
        if ($fleetWrapper === null) {
            throw new RuntimeException('ship not in fleet');
        }

        $success = false;
        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            $warpdrive = $wrapper->getWarpDriveSystemData();

            if ($warpdrive !== null) {
                $success = true;
                $warpdrive->setWarpdriveSplit($warpsplit)->update();
            }
        }

        // only show info if at least one ship was able to change
        if (!$success) {
            return;
        }

        $game->addInformation(sprintf(_('Flottenbefehl ausgeführt: Reaktorleistung geht zu %d Prozent in den Warpantrieb'), 100 - $warpsplit));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
