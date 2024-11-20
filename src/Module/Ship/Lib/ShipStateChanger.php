<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Override;
use Stu\Component\Ship\Mining\CancelMiningInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\Retrofit\CancelRetrofit;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\Interaction\TholianWebUtilInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipStateChanger implements ShipStateChangerInterface
{
    public function __construct(private CancelMiningInterface $cancelMining, private CancelRepairInterface $cancelRepair, private AstroEntryLibInterface $astroEntryLib, private ShipRepositoryInterface $shipRepository, private TholianWebUtilInterface $tholianWebUtil, private ShipTakeoverManagerInterface $shipTakeoverManager, private CancelRetrofitInterface $cancelRetrofit) {}

    #[Override]
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
        } elseif ($currentState === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            $this->astroEntryLib->cancelAstroFinalizing($wrapper);
        } elseif ($currentState === ShipStateEnum::SHIP_STATE_RETROFIT) {
            $this->cancelRetrofit->cancelRetrofit($ship);
        } elseif ($currentState === ShipStateEnum::SHIP_STATE_WEB_SPINNING) {
            $this->tholianWebUtil->releaseWebHelper($wrapper);
        } elseif ($currentState === ShipStateEnum::SHIP_STATE_ACTIVE_TAKEOVER) {
            $this->shipTakeoverManager->cancelTakeover(
                $ship->getTakeoverActive(),
                null,
                true
            );
        } elseif ($currentState === ShipStateEnum::SHIP_STATE_GATHER_RESOURCES) {
            $this->cancelMining->cancelMining($ship, $wrapper);
        }

        $ship->setState($newState);
        $this->shipRepository->save($ship);
    }

    #[Override]
    public function changeAlertState(
        ShipWrapperInterface $wrapper,
        ShipAlertStateEnum $alertState
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
            $this->consumeEnergyForAlertChange($wrapper, ShipStateChangerInterface::ALERT_YELLOW_EPS_USAGE);
        }
        if (
            $alertState == ShipAlertStateEnum::ALERT_RED
            && $currentAlertState !== ShipAlertStateEnum::ALERT_RED
        ) {
            $this->consumeEnergyForAlertChange($wrapper, ShipStateChangerInterface::ALERT_RED_EPS_USAGE);
        }

        // cancel repair if not on alert green
        if ($alertState !== ShipAlertStateEnum::ALERT_GREEN && $this->cancelRepair->cancelRepair($ship)) {
            $msg = _('Die Reparatur wurde abgebrochen');
        }

        if ($alertState !== ShipAlertStateEnum::ALERT_GREEN && $this->cancelRetrofit->cancelRetrofit($ship)) {
            $msg = _('Die Umrüstung wurde abgebrochen');
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