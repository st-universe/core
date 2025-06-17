<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\RepairShip;

use Override;
use request;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Station\View\ShowShipRepair\ShowShipRepair;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class RepairShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REPAIR_SHIP';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private ShipRepositoryInterface $shipRepository,
        private StationUtilityInterface $stationUtility,
        private StationShipRepairRepositoryInterface $stationShipRepairRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipRepair::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $stationId = request::indInt('id');
        $shipId = request::getIntFatal('ship_id');

        $wrappers = $this->stationLoader->getWrappersBySourceAndUserAndTarget(
            $stationId,
            $userId,
            $shipId
        );

        $wrapper = $wrappers->getSource();
        $station = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $ship = $targetWrapper->get();
        if (!$ship instanceof ShipInterface) {
            return;
        }

        if (!$this->stationUtility->canRepairShips($station)) {
            return;
        }

        /**@var array<int, ShipWrapperInterface> */
        $repairableShiplist = [];
        foreach ($station->getDockedShips() as $dockedShip) {
            $wrapper = $this->spacecraftWrapperFactory->wrapShip($dockedShip);
            if (
                !$wrapper->canBeRepaired() || $dockedShip->isUnderRepair()
            ) {
                continue;
            }
            $repairableShiplist[$dockedShip->getId()] = $wrapper;
        }

        if (!array_key_exists($ship->getId(), $repairableShiplist)) {
            $game->addInformation(_('Das Schiff kann nicht repariert werden.'));
            return;
        }

        if ($ship->getState() === SpacecraftStateEnum::ASTRO_FINALIZING) {
            $game->addInformation(_('Das Schiff kartographiert derzeit und kann daher nicht repariert werden.'));
            return;
        }

        $obj = $this->stationShipRepairRepository->prototype();
        $obj->setStation($station);
        $obj->setShip($ship);
        $this->stationShipRepairRepository->save($obj);

        $ship->setState(SpacecraftStateEnum::REPAIR_PASSIVE);

        $this->shipRepository->save($ship);

        $jobs = $this->stationShipRepairRepository->getByStation(
            $station->getId(),
        );

        if (count($jobs) > 1) {
            $game->addInformation(_('Das Schiff wurde zur Reparaturwarteschlange hinzugefügt'));
            return;
        }

        $wrapper = $repairableShiplist[$ship->getId()];
        $ticks = $wrapper->getRepairDuration();
        $game->addInformationf(_('Das Schiff wird repariert. Fertigstellung in %d Runden'), $ticks);

        $this->privateMessageSender->send(
            $userId,
            $ship->getUser()->getId(),
            sprintf(
                "Die %s wird in Sektor %s bei der %s %s des Spielers %s repariert. Fertigstellung in %d Runden.",
                $ship->getName(),
                $ship->getSectorString(),
                $station->getRump()->getName(),
                $station->getName(),
                $station->getUser()->getName(),
                $ticks
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
