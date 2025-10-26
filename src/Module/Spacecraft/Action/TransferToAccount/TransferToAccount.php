<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\TransferToAccount;

use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class TransferToAccount implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRANSFER_TO_ACCOUNT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private TradeLicenseRepositoryInterface $tradeLicenseRepository,
        private TradeLibFactoryInterface $tradeLibFactory,
        private TradePostRepositoryInterface $tradePostRepository,
        private StorageManagerInterface $storageManager,
        private InteractionCheckerInterface $interactionChecker
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $spacecraft = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $tradepost = $this->tradePostRepository->find(request::indInt('postid'));
        if ($tradepost === null) {
            return;
        }

        if (!$this->interactionChecker->checkPosition($spacecraft, $tradepost->getStation())) {
            return;
        }

        if ($spacecraft->isCloaked()) {
            $game->getInfo()->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($spacecraft->isWarped()) {
            $game->getInfo()->addInformation("Schiff befindet sich im Warp");
            return;
        }
        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, $tradepost->getId())) {
            return;
        }

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradepost, $game->getUser());

        if ($storageManager->getFreeStorage() <= 0) {
            $game->getInfo()->addInformation(_('Dein Warenkonto an diesem Posten ist voll'));
            return;
        }
        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');

        $shipStorage = $spacecraft->getStorage();

        if ($shipStorage->isEmpty()) {
            $game->getInfo()->addInformation(_("Keine Waren zum Transferieren vorhanden"));
            return;
        }
        if (count($commodities) == 0 || count($gcount) == 0) {
            $game->getInfo()->addInformation(_("Es wurden keine Waren zum Transferieren ausgewählt"));
            return;
        }
        $game->getInfo()->addInformation(_("Es wurden folgende Waren ins Warenkonto transferiert"));

        foreach ($commodities as $key => $value) {
            $commodityId = (int) $value;
            if (!array_key_exists($key, $gcount)) {
                continue;
            }
            $storage = $shipStorage[$commodityId] ?? null;
            if ($storage === null) {
                continue;
            }
            $count = $gcount[$key];

            $commodity = $storage->getCommodity();

            $count = $count == "max" ? $storage->getAmount() : (int) $count;
            if ($count < 1 || $storageManager->getFreeStorage() <= 0) {
                continue;
            }
            if (!$commodity->isBeamable()) {
                $game->getInfo()->addInformationf(_('%s ist nicht beambar'), $commodity->getName());
                continue;
            }
            $count = min($count, $storage->getAmount());
            if ($storageManager->getStorageSum() + $count > $tradepost->getStorage()) {
                $count = $tradepost->getStorage() - $storageManager->getStorageSum();
            }
            $game->getInfo()->addInformationf(_('%d %s'), $count, $commodity->getName());
            $this->storageManager->lowerStorage($spacecraft, $commodity, $count);
            $storageManager->upperStorage((int) $value, $count);
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
