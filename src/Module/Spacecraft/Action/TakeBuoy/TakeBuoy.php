<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\TakeBuoy;

use Override;
use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\BuoyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class TakeBuoy implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TAKE_BUOY';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private BuoyRepositoryInterface $buoyRepository,
        private CommodityRepositoryInterface $commodityRepository,
        private StorageManagerInterface $storageManager,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
        $userId = $game->getUser()->getId();
        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $ship = $wrapper->get();

        $buoy = $ship->getLocation()->getBuoys()->get(request::indInt('buoyid'));
        if ($buoy === null) {
            $game->addInformation("Die Boje existiert nicht");
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();

        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->isCloaked()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->isWarped()) {
            $game->addInformation("Schiff befindet sich im Warp");
            return;
        }
        if ($ship->isShielded()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }

        if ($epsSystem->getEps() < 1) {
            $game->addInformation(_('Es wird 1 Energie für den Start der Boje benötigt'));
            return;
        }

        $commodity = $this->commodityRepository->find(CommodityTypeEnum::BASE_ID_BUOY);

        if ($commodity !== null) {
            $this->storageManager->upperStorage(
                $ship,
                $commodity,
                1
            );
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
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                null
            );
        }

        $this->buoyRepository->delete($buoy);

        $epsSystem->lowerEps(1)->update();

        $game->addInformation('Die Boje wurde erfolgreich eingesammelt');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
