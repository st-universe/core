<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\TransferFromAccount;

use Override;
use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class TransferFromAccount implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRANSFER_FROM_ACCOUNT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private TradeLicenseRepositoryInterface $tradeLicenseRepository,
        private TradeLibFactoryInterface $tradeLibFactory,
        private TradePostRepositoryInterface $tradePostRepository,
        private StorageManagerInterface $storageManager,
        private InteractionCheckerInterface $interactionChecker
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $spacecraft = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $tradepost = $this->tradePostRepository->find(request::postIntFatal('postid'));
        if ($tradepost === null) {
            return;
        }

        if (!$this->interactionChecker->checkPosition($spacecraft, $tradepost->getStation())) {
            return;
        }

        if ($spacecraft->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($spacecraft->isWarped()) {
            $game->addInformation("Schiff befindet sich im Warp");
            return;
        }
        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, $tradepost->getId())) {
            return;
        }
        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradepost, $game->getUser());
        /** @var array<int, StorageInterface> */
        $curCommodities = $storageManager->getStorage()->toArray();

        if ($curCommodities === []) {
            $game->addInformation(_("Keine Waren zum Transferieren vorhanden"));
            return;
        }
        if (count($commodities) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurden keine Waren zum Transferieren ausgewählt"));
            return;
        }

        $game->addInformation(_("Es wurden folgende Waren vom Warenkonto transferiert"));
        foreach ($commodities as $key => $value) {
            if (!array_key_exists($key, $gcount)) {
                continue;
            }
            if (!array_key_exists($value, $curCommodities)) {
                continue;
            }
            $count = $gcount[$key];
            $count = $count == "max" ? $curCommodities[$value]->getAmount() : (int) $count;
            if ($count < 1 || $spacecraft->getStorageSum() >= $spacecraft->getMaxStorage()) {
                continue;
            }

            $commodity = $curCommodities[$value]->getCommodity();

            if (!$commodity->isBeamable()) {
                $game->addInformation($commodity->getName() . " ist nicht beambar");
                continue;
            }
            if ($commodity->isIllegal($tradepost->getTradeNetwork())) {
                $game->addInformation($commodity->getName() . ' ist in diesem Handelsnetzwerk illegal und kann nicht gehandelt werden');
                continue;
            }
            if ($count > $curCommodities[$value]->getAmount()) {
                $count = $curCommodities[$value]->getAmount();
            }
            if ($spacecraft->getStorageSum() + $count > $spacecraft->getMaxStorage()) {
                $count = $spacecraft->getMaxStorage() - $spacecraft->getStorageSum();
            }

            $storageManager->lowerStorage((int) $value, $count);
            $this->storageManager->upperStorage($spacecraft, $commodity, $count);

            $game->addInformation($count . " " . $curCommodities[$value]->getCommodity()->getName());
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
