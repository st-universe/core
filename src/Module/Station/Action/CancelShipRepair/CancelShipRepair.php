<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\CancelShipRepair;

use Override;
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
    public const string ACTION_IDENTIFIER = 'B_CANCEL_REPAIR';

    public function __construct(private StationShipRepairRepositoryInterface $stationShipRepairRepository, private CancelRepairInterface $cancelRepair, private PrivateMessageSenderInterface $privateMessageSender)
    {
    }

    #[Override]
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
