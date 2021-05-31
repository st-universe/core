<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CancelShipRepair;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class CancelShipRepair implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_REPAIR';

    private CancelShipRepairRequestInterface $request;

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        CancelShipRepairRequestInterface $request,
        ShipLoaderInterface $shipLoader
    ) {
        $this->request = $request;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
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
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
