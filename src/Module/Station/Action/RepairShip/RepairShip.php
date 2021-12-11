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
use Stu\Module\Station\View\ShowShipRepair\ShowShipRepair;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class RepairShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_REPAIR_SHIP';

    private ShipLoaderInterface $shipLoader;

    private StationUtilityInterface $stationUtility;

    private StationShipRepairRepositoryInterface $stationShipRepairRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        StationUtilityInterface $stationUtility,
        StationShipRepairRepositoryInterface $stationShipRepairRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->stationUtility = $stationUtility;
        $this->stationShipRepairRepository = $stationShipRepairRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipRepair::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if (!$this->stationUtility->canRepairShips($station)) {
            return;
        }

        $repairableShiplist = [];
        foreach ($station->getDockedShips() as $ship) {
            if (
                !$ship->canBeRepaired() || $ship->getState() == ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE
                || $ship->getState() == ShipStateEnum::SHIP_STATE_REPAIR_ACTIVE
            ) {
                continue;
            }
            $repairableShiplist[$ship->getId()] = $ship;
        }

        $ship = $this->shipLoader->find((int) request::getIntFatal('ship_id'));
        if ($ship === null || !array_key_exists($ship->getId(), $repairableShiplist)) {
            return;
        }
        if (!$ship->canBeRepaired()) {
            $game->addInformation(_('Das Schiff kann nicht repariert werden.'));
            return;
        }
        if ($ship->getState() == ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
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

        $ticks = $ship->getRepairDuration();
        $game->addInformationf(_('Das Schiff wird repariert. Fertigstellung in %d Runden'), $ticks);

        if ($ship->getUser()->getId() != $userId) {
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
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
