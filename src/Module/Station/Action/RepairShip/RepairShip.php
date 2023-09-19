<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\RepairShip;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Station\View\ShowShipRepair\ShowShipRepair;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class RepairShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_REPAIR_SHIP';

    private ShipLoaderInterface $shipLoader;

    private StationUtilityInterface $stationUtility;

    private StationShipRepairRepositoryInterface $stationShipRepairRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        StationUtilityInterface $stationUtility,
        StationShipRepairRepositoryInterface $stationShipRepairRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->stationUtility = $stationUtility;
        $this->stationShipRepairRepository = $stationShipRepairRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipRepair::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $stationId = request::indInt('id');
        $shipId = request::getIntFatal('ship_id');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
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

        if (!$this->stationUtility->canRepairShips($station)) {
            return;
        }

        /**@var array<int, ShipWrapperInterface> */
        $repairableShiplist = [];
        foreach ($station->getDockedShips() as $dockedShip) {
            $wrapper = $this->shipWrapperFactory->wrapShip($dockedShip);
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

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            $game->addInformation(_('Das Schiff kartographiert derzeit und kann daher nicht repariert werden.'));
            return;
        }

        $obj = $this->stationShipRepairRepository->prototype();
        $obj->setStation($station);
        $obj->setShip($ship);
        $this->stationShipRepairRepository->save($obj);

        $ship->setState(ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE);

        $this->shipLoader->save($ship);

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
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
