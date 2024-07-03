<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CancelShipRepair;

use Override;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;

final class CancelShipRepair implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CANCEL_REPAIR';


    public function __construct(private CancelShipRepairRequestInterface $request, private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository, private CancelRepairInterface $cancelRepair, private PrivateMessageSenderInterface $privateMessageSender)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $shipId = $this->request->getShipId();

        $obj = $this->colonyShipRepairRepository->getByShip($shipId);

        if ($obj === null) {
            throw new SanityCheckException('ship is not under colony repair');
        }

        $ship = $obj->getShip();
        $colony = $obj->getColony();

        if ($ship->getState() !== ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE) {
            return;
        }

        if ($colony->getUserId() !== $userId) {
            return;
        }

        $this->cancelRepair->cancelRepair($ship);
        $game->addInformation(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getName()));

        $this->privateMessageSender->send(
            $userId,
            $ship->getUser()->getId(),
            sprintf("Die Reparatur der %s in Sektor %s wurde abgebrochen.", $ship->getName(), $ship->getSectorString()),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
