<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipStateChanger implements ShipStateChangerInterface
{
    private CancelRepairInterface $cancelRepair;

    private AstroEntryLibInterface $astroEntryLib;

    private ShipRepositoryInterface $shipRepository;

    private TholianWebUtilInterface $tholianWebUtil;

    public function __construct(
        CancelRepairInterface $cancelRepair,
        AstroEntryLibInterface $astroEntryLib,
        ShipRepositoryInterface $shipRepository,
        TholianWebUtilInterface $tholianWebUtil
    ) {
        $this->cancelRepair = $cancelRepair;
        $this->astroEntryLib = $astroEntryLib;
        $this->shipRepository = $shipRepository;
        $this->tholianWebUtil = $tholianWebUtil;
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

        $ship->setState($newState);
        $this->shipRepository->save($ship);
    }
}
