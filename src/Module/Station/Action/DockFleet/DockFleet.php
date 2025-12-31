<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\DockFleet;

use request;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Component\Spacecraft\System\Exception\SpacecraftSystemException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class DockFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DOCK_FLEET';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private FleetRepositoryInterface $fleetRepository,
        private ShipRepositoryInterface $shipRepository,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private InteractionCheckerInterface $interactionChecker,
        private CancelRepairInterface $cancelRepair,
        private CancelRetrofitInterface $cancelRetrofit,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
    ) {
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->stationLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $station = $wrapper->get();

        $targetFleet = $this->fleetRepository->find(request::getIntFatal('fid'));
        if ($targetFleet === null) {
            return;
        }
        if (!$this->interactionChecker->checkPosition($targetFleet->getLeadShip(), $station)) {
            return;
        }
        if ($targetFleet->getUser()->getId() !== $game->getUser()->getId()) {
            return;
        }
        if (!$station->isStation()) {
            return;
        }

        if (!$station->hasEnoughCrew($game)) {
            return;
        }

        if ($station->isShielded()) {
            $game->getInfo()->addInformation(_("Aktion nicht möglich. Die Station hat die Schilde aktiviert"));
            return;
        }

        $this->fleetDock($wrapper, $targetFleet, $game);
    }

    private function fleetDock(StationWrapperInterface $stationWrapper, Fleet $targetFleet, GameControllerInterface $game): void
    {
        $station = $stationWrapper->get();
        $epsSystem = $stationWrapper->getEpsSystemData();

        $msg = [_("Station aktiviert Andockleitsystem zur Flotte: ") . $targetFleet->getName()];
        $freeSlots = $station->getFreeDockingSlotCount();
        foreach ($targetFleet->getShips() as $ship) {
            if ($freeSlots <= 0) {
                $msg[] = _("Es sind alle Dockplätze belegt");
                break;
            }
            if ($ship->getDockedTo() !== null) {
                continue;
            }

            if ($epsSystem === null || $epsSystem->getEps() < Spacecraft::SYSTEM_ECOST_DOCK) {
                $msg[] = $station->getName() . _(": Nicht genügend Energie vorhanden");
                break;
            }
            if ($ship->isCloaked()) {
                $msg[] = $ship->getName() . _(': Das Schiff ist getarnt');
                continue;
            }
            if ($this->cancelRepair->cancelRepair($ship)) {
                $msg[] = $ship->getName() . _(': Die Reparatur wurde abgebrochen');
            }
            if ($this->cancelRetrofit->cancelRetrofit($ship)) {
                $msg[] = $ship->getName() . _(': Die Umrüstung wurde abgebrochen');
            }

            $wrapper = $this->spacecraftWrapperFactory->wrapShip($ship);

            try {
                $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::SHIELDS);
            } catch (SpacecraftSystemException) {
                // nothing to do
            }

            try {
                $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::WARPDRIVE);
            } catch (SpacecraftSystemException) {
                // nothing to do
            }

            $ship->setDockedTo($station);

            $epsSystem->lowerEps(Spacecraft::SYSTEM_ECOST_DOCK);

            $this->shipRepository->save($ship);

            $freeSlots--;
        }

        if ($epsSystem !== null) {
            $epsSystem->update();
        }

        $game->getInfo()->addInformationArray($msg, true);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
