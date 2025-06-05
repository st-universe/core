<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Override;
use Stu\Component\Ship\Mining\CancelMiningInterface;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Exception\InsufficientEnergyException;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class SpacecraftStateChanger implements SpacecraftStateChangerInterface
{
    public function __construct(
        private CancelMiningInterface $cancelMining,
        private CancelRepairInterface $cancelRepair,
        private AstroEntryLibInterface $astroEntryLib,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private TholianWebUtilInterface $tholianWebUtil,
        private ShipTakeoverManagerInterface $shipTakeoverManager,
        private CancelRetrofitInterface $cancelRetrofit
    ) {}

    #[Override]
    public function changeState(SpacecraftWrapperInterface $wrapper, SpacecraftStateEnum $newState): void
    {
        $ship = $wrapper->get();
        $currentState = $ship->getState();

        //nothing to do
        if (
            $currentState === SpacecraftStateEnum::DESTROYED
            || $currentState === $newState
        ) {
            return;
        }

        //repair stuff
        if ($ship->isUnderRepair()) {
            $this->cancelRepair->cancelRepair($ship);
        } elseif ($currentState === SpacecraftStateEnum::ASTRO_FINALIZING) {
            $this->astroEntryLib->cancelAstroFinalizing($wrapper);
        } elseif ($currentState === SpacecraftStateEnum::RETROFIT && $ship instanceof ShipInterface) {
            $this->cancelRetrofit->cancelRetrofit($ship);
        } elseif ($currentState === SpacecraftStateEnum::WEB_SPINNING && $wrapper instanceof ShipWrapperInterface) {
            $this->tholianWebUtil->releaseWebHelper($wrapper);
        } elseif ($currentState === SpacecraftStateEnum::ACTIVE_TAKEOVER) {
            $this->shipTakeoverManager->cancelTakeover(
                $ship->getTakeoverActive(),
                null,
                true
            );
        } elseif ($currentState === SpacecraftStateEnum::GATHER_RESOURCES && $wrapper instanceof ShipWrapperInterface) {
            $this->cancelMining->cancelMining($wrapper);
        }

        $ship->setState($newState);
        $this->spacecraftRepository->save($ship);
    }

    #[Override]
    public function changeAlertState(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftAlertStateEnum $alertState
    ): ?string {

        $currentAlertState = $wrapper->getAlertState();

        //nothing to do
        if ($currentAlertState === $alertState) {
            return null;
        }

        if (!$wrapper->get()->hasComputer()) {
            return null;
        }

        //check if enough energy
        if (
            $alertState === SpacecraftAlertStateEnum::ALERT_YELLOW
            && $currentAlertState === SpacecraftAlertStateEnum::ALERT_GREEN
        ) {
            $this->consumeEnergyForAlertChange($wrapper, $alertState->getEpsUsage());
        }
        if (
            $alertState === SpacecraftAlertStateEnum::ALERT_RED
            && $currentAlertState !== SpacecraftAlertStateEnum::ALERT_RED
        ) {
            $this->consumeEnergyForAlertChange($wrapper, $alertState->getEpsUsage());
        }

        $msg = null;
        $ship = $wrapper->get();

        // cancel repair if not on alert green
        if ($alertState !== SpacecraftAlertStateEnum::ALERT_GREEN && $this->cancelRepair->cancelRepair($ship)) {
            $msg = _('Die Reparatur wurde abgebrochen');
        }

        if (
            $alertState !== SpacecraftAlertStateEnum::ALERT_GREEN
            && $ship instanceof ShipInterface
            && $this->cancelRetrofit->cancelRetrofit($ship)
        ) {
            $msg = _('Die Umrüstung wurde abgebrochen');
        }

        // now change
        $wrapper->getComputerSystemDataMandatory()->setAlertState($alertState)->update();

        return $msg;
    }

    private function consumeEnergyForAlertChange(SpacecraftWrapperInterface $wrapper, int $amount): void
    {
        $eps = $wrapper->getEpsSystemData();

        if ($eps === null || $eps->getEps() < $amount) {
            throw new InsufficientEnergyException($amount);
        }
        $eps->lowerEps($amount)->update();
    }
}
