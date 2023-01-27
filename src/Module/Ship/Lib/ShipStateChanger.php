<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\ShipStateEnum;
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
        if ($currentState === $newState) {
            return;
        }

        //repair stuff
        $this->cancelRepair->cancelRepair($ship);

        //mapping stuff
        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }

        //web spinning
        if ($ship->getState() === ShipStateEnum::SHIP_STATE_WEB_SPINNING) {
            $this->tholianWebUtil->releaseWebHelper($wrapper);
        }

        //emergency
        if ($ship->getState() === ShipStateEnum::SHIP_STATE_EMERGENCY) {
            $emergency = $this->spacecraftEmergencyRepository->getByShipId($ship->getId());

            if ($emergency !== null) {
                $emergency->setDeleted($this->stuTime->time());
                $this->spacecraftEmergencyRepository->save($emergency);
            }
        }

        $ship->setState($newState);
        $this->shipRepository->save($ship);
    }
}
