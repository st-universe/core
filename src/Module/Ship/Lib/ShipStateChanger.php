<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;

final class ShipStateChanger implements ShipStateChangerInterface
{
    private CancelRepairInterface $cancelRepair;

    private AstroEntryLibInterface $astroEntryLib;

    private ShipRepositoryInterface $shipRepository;

    private TholianWebUtilInterface $tholianWebUtil;

    private SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository;

    private StuTime $stuTime;

    public function __construct(
        CancelRepairInterface $cancelRepair,
        AstroEntryLibInterface $astroEntryLib,
        ShipRepositoryInterface $shipRepository,
        TholianWebUtilInterface $tholianWebUtil,
        SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository,
        StuTime $stuTime
    ) {
        $this->cancelRepair = $cancelRepair;
        $this->astroEntryLib = $astroEntryLib;
        $this->shipRepository = $shipRepository;
        $this->tholianWebUtil = $tholianWebUtil;
        $this->spacecraftEmergencyRepository = $spacecraftEmergencyRepository;
        $this->stuTime = $stuTime;
    }

    public function changeShipState(ShipWrapperInterface $wrapper, int $newState): void
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
        if ($currentState === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }

        //web spinning
        elseif ($currentState === ShipStateEnum::SHIP_STATE_WEB_SPINNING) {
            $this->tholianWebUtil->releaseWebHelper($wrapper);
        }

        //emergency
        elseif ($currentState === ShipStateEnum::SHIP_STATE_EMERGENCY) {
            $emergency = $this->spacecraftEmergencyRepository->getByShipId($ship->getId());

            if ($emergency !== null) {
                $emergency->setDeleted($this->stuTime->time());
                $this->spacecraftEmergencyRepository->save($emergency);
            }
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
        if ($alertState !== ShipAlertStateEnum::ALERT_GREEN) {
            if ($this->cancelRepair->cancelRepair($ship)) {
                $msg = _('Die Reparatur wurde abgebrochen');
            }
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
