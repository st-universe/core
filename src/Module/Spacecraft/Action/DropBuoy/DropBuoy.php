<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DropBuoy;

use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\BuoyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class DropBuoy implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DROP_BOUY';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private BuoyRepositoryInterface $buoyRepository,
        private CommodityRepositoryInterface $commodityRepository,
        private StorageManagerInterface $storageManager
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);
        $userId = $context->getUser()->getId();
        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        if (!$ship->isSystemHealthy(SpacecraftSystemTypeEnum::TORPEDO)) {
            $context->getInfo()->addInformation(_("Keine nutzbare Torpedorampe vorhanden"));
            return;
        }
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() === 0) {
            $context->getInfo()->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->isCloaked()) {
            $context->getInfo()->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->isWarped()) {
            $context->getInfo()->addInformation("Schiff befindet sich im Warp");
            return;
        }
        if ($ship->isShielded()) {
            $context->getInfo()->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }
        if (count($this->buoyRepository->findByUserId($userId)) >= 16) {
            $context->getInfo()->addInformation(_("Es können nicht mehr als 16 Bojen platziert werden"));
            return;
        }

        $text = request::postString('text');

        if ($text === false || mb_strlen($text) > 60) {
            $context->getInfo()->addInformation(_("Der Text darf nicht länger als 60 Zeichen sein"));
            return;
        }

        if ($text === '' || $text === '0') {
            $context->getInfo()->addInformation(_("Der Text darf nicht leer sein"));
            return;
        }

        $storage = $ship->getStorage();

        $commodity = $this->commodityRepository->find(CommodityTypeConstants::BASE_ID_BUOY);
        if ($commodity !== null && !$storage->containsKey($commodity->getId())) {
            $context->getInfo()->addInformationf(
                _('Es wird eine Boje benötigt')
            );
            return;
        }

        if ($epsSystem->getEps() < 1) {
            $context->getInfo()->addInformation(_('Es wird 1 Energie für den Start der Boje benötigt'));
            return;
        }

        if ($commodity !== null) {
            $this->storageManager->lowerStorage(
                $ship,
                $commodity,
                1
            );
        }

        $buoy = $this->buoyRepository->prototype();
        $buoy->setUser($context->getUser());
        $buoy->setText($text);
        $buoy->setLocation($ship->getLocation());


        $this->buoyRepository->save($buoy);
        $epsSystem->lowerEps(1)->update();

        $context->getInfo()->addInformation(_('Die Boje wurde erfolgreich platziert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
