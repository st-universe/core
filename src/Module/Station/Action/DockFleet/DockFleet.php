<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\DockFleet;

use Override;
use request;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class DockFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DOCK_FLEET';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private FleetRepositoryInterface $fleetRepository,
        private ShipRepositoryInterface $shipRepository,
        private ShipSystemManagerInterface $shipSystemManager,
        private InteractionCheckerInterface $interactionChecker,
        private CancelRepairInterface $cancelRepair,
        private CancelRetrofitInterface $cancelRetrofit,
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        //$this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $station = $wrapper->get();

        $this->loggerUtil->log('A');
        $targetFleet = $this->fleetRepository->find(request::getIntFatal('fid'));
        if ($targetFleet === null) {
            $this->loggerUtil->log('B');
            return;
        }
        if (!$this->interactionChecker->checkPosition($targetFleet->getLeadShip(), $station)) {
            $this->loggerUtil->log('C');
            return;
        }
        if ($targetFleet->getUser() !== $game->getUser()) {
            $this->loggerUtil->log('D');
            return;
        }
        if (!$station->isBase()) {
            $this->loggerUtil->log('E');
            return;
        }

        if (!$station->hasEnoughCrew($game)) {
            return;
        }

        if ($station->getShieldState()) {
            $game->addInformation(_("Aktion nicht möglich. Die Station hat die Schilde aktiviert"));
            return;
        }

        $this->fleetDock($wrapper, $targetFleet, $game);
    }

    private function fleetDock(ShipWrapperInterface $stationWrapper, FleetInterface $targetFleet, GameControllerInterface $game): void
    {
        $station = $stationWrapper->get();
        $epsSystem = $stationWrapper->getEpsSystemData();

        $this->loggerUtil->log('F');
        $msg = [_("Station aktiviert Andockleitsystem zur Flotte: ") . $targetFleet->getName()];
        $freeSlots = $station->getFreeDockingSlotCount();
        foreach ($targetFleet->getShips() as $ship) {
            $this->loggerUtil->log('G');
            if ($freeSlots <= 0) {
                $msg[] = _("Es sind alle Dockplätze belegt");
                break;
            }
            if ($ship->getDockedTo() !== null) {
                continue;
            }

            if ($epsSystem === null || $epsSystem->getEps() < ShipEnum::SYSTEM_ECOST_DOCK) {
                $msg[] = $station->getName() . _(": Nicht genügend Energie vorhanden");
                break;
            }
            if ($ship->getCloakState()) {
                $msg[] = $ship->getName() . _(': Das Schiff ist getarnt');
                continue;
            }
            if ($this->cancelRepair->cancelRepair($ship)) {
                $msg[] = $ship->getName() . _(': Die Reparatur wurde abgebrochen');
            }
            if ($this->cancelRetrofit->cancelRetrofit($ship)) {
                $msg[] = $ship->getName() . _(': Die Umrüstung wurde abgebrochen');
            }

            $wrapper = $this->shipWrapperFactory->wrapShip($ship);

            try {
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS);
            } catch (ShipSystemException) {
            }

            try {
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            } catch (ShipSystemException) {
            }

            $ship->setDockedTo($station);

            $epsSystem->lowerEps(ShipEnum::SYSTEM_ECOST_DOCK);

            $this->shipRepository->save($ship);

            $freeSlots--;
        }

        if ($epsSystem !== null) {
            $epsSystem->update();
        }

        $game->addInformationMerge($msg);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}