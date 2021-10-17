<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CancelShipRepair;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;

final class CancelShipRepair implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_REPAIR';

    private CancelShipRepairRequestInterface $request;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository;

    public function __construct(
        CancelShipRepairRequestInterface $request,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->request = $request;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $shipId = $this->request->getShipId();

        $obj = $this->colonyShipRepairRepository->getByShip($shipId);
        $ship = $obj->getShip();
        $colony = $obj->getColony();

        if ($ship->getState() !== ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE) {
            return;
        }

        if ($colony->getUserId() != $userId) {
            return;
        }

        $ship->cancelRepair();
        $game->addInformation(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getName()));

        if ($ship->getUser()->getId() != $userId) {
            $this->privateMessageSender->send(
                $userId,
                $ship->getUser()->getId(),
                sprintf("Die Reparatur der %s in Sektor %s wurde abgebrochen.", $ship->getName(), $ship->getSectorString()),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
