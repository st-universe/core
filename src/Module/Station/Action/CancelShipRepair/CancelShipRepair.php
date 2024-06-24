<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\CancelShipRepair;

use request;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class CancelShipRepair implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_REPAIR';

    private PrivateMessageSenderInterface $privateMessageSender;

    private CancelRepairInterface $cancelRepair;

    private StationShipRepairRepositoryInterface $stationShipRepairRepository;

    public function __construct(
        StationShipRepairRepositoryInterface $stationShipRepairRepository,
        CancelRepairInterface $cancelRepair,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->stationShipRepairRepository = $stationShipRepairRepository;
        $this->cancelRepair = $cancelRepair;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $targetId = request::getIntFatal('shipid');

        $repairJob = $this->stationShipRepairRepository->getByShip($targetId);

        if ($repairJob === null) {
            return;
        }

        $station = $repairJob->getStation();
        $target = $repairJob->getShip();

        if ($target->getState() !== ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE) {
            return;
        }

        if ($station->getUser() !== $game->getUser()) {
            return;
        }

        $this->cancelRepair->cancelRepair($target);
        $game->addInformation(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $target->getName()));

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            sprintf("Die Reparatur der %s in Sektor %s wurde abgebrochen.", $target->getName(), $target->getSectorString()),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
