<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\DockTractoredShip;

use Override;
use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class DockTractoredShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DOCK_TRACTORED';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private ActivatorDeactivatorHelperInterface $helper
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $stationId = request::getIntFatal('id');

        $wrapper = $this->stationLoader->getWrapperByIdAndUser(
            $stationId,
            $userId
        );
        $station = $wrapper->get();

        $tractoredShip = $station->getTractoredShip();

        // sanity checks
        if ($tractoredShip === null) {
            return;
        }
        if (!$station->hasEnoughCrew($game)) {
            return;
        }

        //check for energy
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < ShipEnum::SYSTEM_ECOST_DOCK) {
            $game->addInformationf('Zum Andocken wird %d Energie benötigt', ShipEnum::SYSTEM_ECOST_DOCK);
            return;
        }
        //check for free dock slots
        if (!$station->hasFreeDockingSlots()) {
            $game->addInformation('Zur Zeit sind alle Dockplätze belegt');
            return;
        }
        // check for fleet state
        if ($tractoredShip->getFleet() !== null && $tractoredShip->getFleet()->getShipCount() > 1) {
            $game->addInformation("Aktion nicht möglich. Das Ziel befindet sich in einer Flotte.");
            return;
        }
        // check for alert green
        if (!$tractoredShip->isAlertGreen()) {
            $game->addInformation("Aktion nicht möglich. Das Ziel ist nicht auf Alarm Grün.");
            return;
        }

        $epsSystem->lowerEps(1)->update();
        $tractoredShip->setDockedTo($station);
        $station->getDockedShips()->set($tractoredShip->getId(), $tractoredShip);

        $this->stationLoader->save($station);
        $this->spacecraftRepository->save($tractoredShip);

        $game->addInformation('Andockvorgang abgeschlossen');
        $this->helper->deactivate($stationId, SpacecraftSystemTypeEnum::TRACTOR_BEAM, $game);

        $this->privateMessageSender->send(
            $userId,
            $tractoredShip->getUser()->getId(),
            sprintf(
                'Die %s wurde an der Station %s angedockt',
                $tractoredShip->getName(),
                $station->getName()
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $tractoredShip->getHref()
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
