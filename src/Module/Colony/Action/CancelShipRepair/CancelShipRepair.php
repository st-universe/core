<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CancelShipRepair;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class CancelShipRepair implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_REPAIR';

    private CancelShipRepairRequestInterface $request;

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        CancelShipRepairRequestInterface $request,
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->request = $request;
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $shipId = $this->request->getShipId();

        $ship = $this->shipLoader->getByIdAndUser(
            $shipId,
            $userId
        );

        if ($ship->getState() !== ShipStateEnum::SHIP_STATE_REPAIR) {
            return;
        }

        $ship->cancelRepair();
        $game->addInformation(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getName()));

        if ($ship->getUserId() != $userId) {
            $this->privateMessageSender->send(
                $userId,
                $ship->getUserId(),
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
