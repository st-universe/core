<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\DockTractoredShip;

use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class DockTractoredShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DOCK_TRACTORED';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ActivatorDeactivatorHelperInterface $helper;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ActivatorDeactivatorHelperInterface $helper
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->helper = $helper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $stationId = request::getIntFatal('id');

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $stationId,
            $userId
        );
        $station = $wrapper->get();

        $tractoredShip = $station->getTractoredShip();

        // sanity checks
        if ($tractoredShip === null) {
            return;
        }
        if (!$station->isBase()) {
            return;
        }
        if (!$station->hasEnoughCrew($game)) {
            return;
        }

        //check for energy
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < ShipEnum::SYSTEM_ECOST_DOCK) {
            $game->addInformation('Zum Andocken wird 1 Energie benötigt');
            return;
        }
        //check for free dock slots
        if (!$station->hasFreeDockingSlots()) {
            $game->addInformation('Zur Zeit sind alle Dockplätze belegt');
            return;
        }
        // check for fleet state
        if ($tractoredShip->getFleet() !== null && $tractoredShip->getFleet()->getShipCount() > 1) {
            $game->addInformation(_("Aktion nicht möglich. Das Ziel befindet sich in einer FLotte."));
            return;
        }
        // check for alert green
        if (!$tractoredShip->isAlertGreen()) {
            $game->addInformation(_("Aktion nicht möglich. Das Ziel ist nicht auf Alarm grün."));
            return;
        }

        $epsSystem->lowerEps(1)->update();
        $tractoredShip->setDockedTo($station);

        $this->shipLoader->save($station);
        $this->shipLoader->save($tractoredShip);

        $game->addInformation('Andockvorgang abgeschlossen');
        $this->helper->deactivate($stationId, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, $game);

        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $tractoredShip->getId());

        $this->privateMessageSender->send(
            $userId,
            $tractoredShip->getUser()->getId(),
            sprintf(
                'Die %s wurde an der Station %s angedockt',
                $tractoredShip->getName(),
                $station->getName()
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $href
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
