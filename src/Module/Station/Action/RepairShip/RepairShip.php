<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\RepairShip;

use request;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Station\View\ShowShipRepair\ShowShipRepair;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class RepairShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REPAIR_SHIP';

    public function __construct(
        private readonly StationLoaderInterface $stationLoader,
        private readonly StationUtilityInterface $stationUtility,
        private readonly StationShipRepairRepositoryInterface $stationShipRepairRepository,
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly PrivateMessageSenderInterface $privateMessageSender,
        private readonly RepairUtilInterface $repairUtil
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipRepair::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $stationId = request::indInt('id');
        $shipId = request::getIntFatal('ship_id');

        $wrappers = $this->stationLoader->getWrappersBySourceAndUserAndTarget($stationId, $userId, $shipId);
        $stationWrapper = $wrappers->getSource();
        $station = $stationWrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }

        $ship = $targetWrapper->get();
        if (!$ship instanceof Ship) {
            return;
        }

        if (!$this->stationUtility->canRepairShips($station)) {
            return;
        }

        /** @var array<int, ShipWrapperInterface> $repairableShiplist */
        $repairableShiplist = [];
        foreach ($station->getDockedShips() as $dockedShip) {
            $wrapper = $this->spacecraftWrapperFactory->wrapShip($dockedShip);
            if (!$wrapper->canBeRepaired() || $dockedShip->getCondition()->isUnderRepair()) {
                continue;
            }

            $repairableShiplist[$dockedShip->getId()] = $wrapper;
        }

        if (!array_key_exists($ship->getId(), $repairableShiplist)) {
            $game->getInfo()->addInformation(_('Das Schiff kann nicht repariert werden.'));
            return;
        }

        if ($ship->getState() === SpacecraftStateEnum::ASTRO_FINALIZING) {
            $game->getInfo()->addInformation(_('Das Schiff kartographiert derzeit und kann daher nicht repariert werden.'));
            return;
        }

        $jobs = $this->stationShipRepairRepository->getByStation($station->getId());
        $isQueued = count($jobs) >= 1;

        $repair = $this->stationShipRepairRepository->prototype();
        $repair->setStation($station);
        $repair->setShip($ship);
        $repair->setFinishTime($isQueued ? 0 : time() + $this->repairUtil->getPassiveRepairStepDuration($ship));
        $repair->setStopDate(0);
        $repair->setIsStopped(false);
        $this->stationShipRepairRepository->save($repair);

        $ship->getCondition()->setState(SpacecraftStateEnum::REPAIR_PASSIVE);

        if ($isQueued) {
            $game->getInfo()->addInformation(_('Das Schiff wurde zur Reparaturwarteschlange hinzugefuegt'));
            return;
        }

        $wrapper = $repairableShiplist[$ship->getId()];
        $estimatedDuration = $this->repairUtil->getPassiveRepairEstimatedDuration($wrapper, false);
        $estimatedFinishDate = date('d.m.Y H:i', time() + $estimatedDuration);

        $game->getInfo()->addInformationf(
            _('Das Schiff wird repariert. Voraussichtliche Fertigstellung: %s'),
            $estimatedFinishDate
        );

        $this->privateMessageSender->send(
            $userId,
            $ship->getUser()->getId(),
            sprintf(
                'Die %s wird in Sektor %s bei der %s %s des Spielers %s repariert. Voraussichtliche Fertigstellung: %s',
                $ship->getName(),
                $ship->getSectorString(),
                $station->getRump()->getName(),
                $station->getName(),
                $station->getUser()->getName(),
                $estimatedFinishDate
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
