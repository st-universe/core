<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\CancelShipRepair;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class CancelShipRepair implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_REPAIR';

    private PrivateMessageSenderInterface $privateMessageSender;

    private StationShipRepairRepositoryInterface $stationShipRepairRepository;

    public function __construct(
        StationShipRepairRepositoryInterface $stationShipRepairRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->stationShipRepairRepository = $stationShipRepairRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $stationId = request::getIntFatal('id');
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

        if ($stationId !== $station->getId()) {
            return;
        }

        $target->cancelRepair();
        $game->addInformation(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $target->getName()));

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            sprintf("Die Reparatur der %s in Sektor %s wurde abgebrochen.", $target->getName(), $target->getSectorString()),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
