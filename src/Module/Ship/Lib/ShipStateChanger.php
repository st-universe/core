<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\Interaction\TholianWebUtilInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipStateChanger implements ShipStateChangerInterface
{
    private CancelRepairInterface $cancelRepair;

    private AstroEntryLibInterface $astroEntryLib;

    private ShipRepositoryInterface $shipRepository;

    private TholianWebUtilInterface $tholianWebUtil;

    private ShipTakeoverManagerInterface $shipTakeoverManager;

    public function __construct(
        CancelRepairInterface $cancelRepair,
        AstroEntryLibInterface $astroEntryLib,
        ShipRepositoryInterface $shipRepository,
        TholianWebUtilInterface $tholianWebUtil,
        ShipTakeoverManagerInterface $shipTakeoverManager
    ) {
        $this->cancelRepair = $cancelRepair;
        $this->astroEntryLib = $astroEntryLib;
        $this->shipRepository = $shipRepository;
        $this->tholianWebUtil = $tholianWebUtil;
        $this->shipTakeoverManager = $shipTakeoverManager;
    }

    public function changeShipState(ShipWrapperInterface $wrapper, ShipStateEnum $newState): void
    {
        $ship = $wrapper->get();
        $currentState = $ship->getState();

        //nothing to do
        if (
            $currentState === ShipStateEnum::SHIP_STATE_DESTROYED
            || $currentState === $newState
        ) {
            return;
        }

        //repair stuff
        if ($ship->isUnderRepair()) {
            $this->cancelRepair->cancelRepair($ship);
        }

        //mapping stuff
        else if ($currentState === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }

        //web spinning
        elseif ($currentState === ShipStateEnum::SHIP_STATE_WEB_SPINNING) {
            $this->tholianWebUtil->releaseWebHelper($wrapper);
        }

        //active takeover
        elseif ($currentState === ShipStateEnum::SHIP_STATE_ACTIVE_TAKEOVER) {
            $this->shipTakeoverManager->cancelTakeover(
                $ship->getTakeoverActive(),
                null,
                true
            );
        }

        $ship->setState($newState);
        $this->shipRepository->save($ship);
    }

    public function changeAlertState(
        ShipWrapperInterface $wrapper,
        int $alertState
    ): ?string {
        $ship = $wrapper->get();

        $msg = null;

        $currentAlertState = $ship->getAlertState();

        //nothing to do
        if ($currentAlertState === $alertState) {
            return null;
        }

        //check if enough energy
        if (
            $alertState == ShipAlertStateEnum::ALERT_YELLOW
            && $currentAlertState == ShipAlertStateEnum::ALERT_GREEN
        ) {
            $this->consumeEnergyForAlertChange($wrapper, 1);
        }
        if (
            $alertState == ShipAlertStateEnum::ALERT_RED
            && $currentAlertState !== ShipAlertStateEnum::ALERT_RED
        ) {
            $this->consumeEnergyForAlertChange($wrapper, 2);
        }

        // cancel repair if not on alert green
        if ($alertState !== ShipAlertStateEnum::ALERT_GREEN && $this->cancelRepair->cancelRepair($ship)) {
            $msg = _('Die Reparatur wurde abgebrochen');
        }

        // now change
        $ship->setAlertState($alertState);

        return $msg;
    }

    private function consumeEnergyForAlertChange(ShipWrapperInterface $wrapper, int $amount): void
    {
        $eps = $wrapper->getEpsSystemData();

        if ($eps === null || $eps->getEps() < $amount) {
            throw new InsufficientEnergyException($amount);
        }
        $eps->lowerEps($amount)->update();
    }
}
