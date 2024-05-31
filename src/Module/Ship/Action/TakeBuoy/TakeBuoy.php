<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TakeBuoy;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\BuoyRepositoryInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;



final class TakeBuoy implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TAKE_BUOY';

    private ShipLoaderInterface $shipLoader;
    private BuoyRepositoryInterface $buoyRepository;
    private CommodityRepositoryInterface $commodityRepository;
    private ShipStorageManagerInterface $shipStorageManager;
    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        BuoyRepositoryInterface $buoyRepository,
        CommodityRepositoryInterface $commodityRepository,
        ShipStorageManagerInterface $shipStorageManager,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->buoyRepository = $buoyRepository;
        $this->commodityRepository = $commodityRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);
        $userId = $game->getUser()->getId();
        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();
        $buoyId = request::indInt('buoy_id');
        $epsSystem = $wrapper->getEpsSystemData();

        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpDriveState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }

        if ($epsSystem->getEps() < 1) {
            $game->addInformation(_('Es wird 1 Energie für den Start der Boje benötigt'));
            return;
        }

        $commodity = $this->commodityRepository->find(CommodityTypeEnum::BASE_ID_BUOY);

        if ($commodity !== null) {
            $this->shipStorageManager->upperStorage(
                $ship,
                $commodity,
                1
            );
        }

        $buoy = $this->buoyRepository->find($buoyId);
        if ($buoy === null) {
            $game->addInformation(_("Die Boje existiert nicht"));
            return;
        }

        if ($buoy->getUserId() !== $userId) {
            $this->privateMessageSender->send(
                $game->getUser()->getId(),
                $buoy->getUserId(),
                sprintf(
                    _('Deine Boje %s wurde von der %s bei %s aufgebracht.'),
                    $buoy->getText(),
                    $ship->getName(),
                    $ship->getSectorString()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                null
            );
        }

        $this->buoyRepository->delete($buoy);

        $epsSystem->lowerEps(1)->update();

        $game->addInformation(_('Die Boje wurde erfolgreich eingesammelt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
