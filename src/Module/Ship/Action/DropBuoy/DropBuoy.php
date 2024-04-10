<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DropBuoy;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\BuoyRepositoryInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Module\Commodity\CommodityTypeEnum;



final class DropBuoy implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DROP_BOUY';

    private ShipLoaderInterface $shipLoader;
    private BuoyRepositoryInterface $buoyRepository;
    private CommodityRepositoryInterface $commodityRepository;
    private ShipStorageManagerInterface $shipStorageManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        BuoyRepositoryInterface $buoyRepository,
        CommodityRepositoryInterface $commodityRepository,
        ShipStorageManagerInterface $shipStorageManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->buoyRepository = $buoyRepository;
        $this->commodityRepository = $commodityRepository;
        $this->shipStorageManager = $shipStorageManager;
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

        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TORPEDO)) {
            $game->addInformation(_("Keine nutzbare Torpedorampe vorhanden"));
            return;
        }
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }
        if (count($this->buoyRepository->findByUserId($userId)) >= 16) {
            $game->addInformation(_("Es können nicht mehr als 16 Bojen platziert werden"));
            return;
        }

        $text = request::postString('text');

        if ($text === false || mb_strlen($text) > 60) {
            $game->addInformation(_("Der Text darf nicht länger als 60 Zeichen sein"));
            return;
        }

        if (empty($text)) {
            $game->addInformation(_("Der Text darf nicht leer sein"));
            return;
        }

        $storage = $ship->getStorage();

        $commodity = $this->commodityRepository->find(CommodityTypeEnum::BASE_ID_BUOY);
        if ($commodity !== null && !$storage->containsKey($commodity->getId())) {
            $game->addInformationf(
                _('Es wird eine Boje benötigt')
            );
            return;
        }

        if ($epsSystem->getEps() < 1) {
            $game->addInformation(_('Es wird 1 Energie für den Start der Boje benötigt'));
            return;
        }

        if ($commodity !== null) {
            $this->shipStorageManager->lowerStorage(
                $ship,
                $commodity,
                1
            );
        }

        $buoy = $this->buoyRepository->prototype();
        $buoy->setUser($game->getUser());
        $buoy->setText($text);


        if ($ship->getStarsystemMap() !== null) {
            $buoy->setSystemMap($ship->getStarsystemMap());
        } else {
            $buoy->setMap($ship->getMap());
        }


        $this->buoyRepository->save($buoy);
        $epsSystem->lowerEps(1)->update();

        $game->addInformation(_('Die Boje wurde erfolgreich platziert'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
