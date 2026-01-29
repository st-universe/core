<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TransferCommodities;

use Stu\Exception\AccessViolationException;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\TradeTransferRepositoryInterface;

final class TransferCommodities implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRANSFER';

    public function __construct(private TransferCommoditiesRequestInterface $transferCommoditiesRequest, private TradeTransferRepositoryInterface $tradeTransferRepository, private TradeLicenseRepositoryInterface $tradeLicenseRepository, private TradeLibFactoryInterface $tradeLibFactory, private TradePostRepositoryInterface $tradePostRepository, private StorageRepositoryInterface $storageRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowAccounts::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $amount = $this->transferCommoditiesRequest->getAmount();
        $destinationTradePostId = $this->transferCommoditiesRequest->getDestinationTradePostId();

        if ($destinationTradePostId == -1) {
            return;
        }

        $storageId = $this->transferCommoditiesRequest->getStorageId();
        $selectedStorage = $this->storageRepository->find($storageId);
        if ($selectedStorage === null) {
            throw new AccessViolationException(sprintf(_('userId %d tried to transfer non-existent storageId %d'), $userId, $storageId));
        }
        if ($selectedStorage->getUserId() !== $userId) {
            throw new AccessViolationException(sprintf(_('userId %d tried to transfer foreign storageId %d'), $userId, $storageId));
        }

        $tradepost = $selectedStorage->getTradePost();
        if ($tradepost === null) {
            throw new SanityCheckException(sprintf('storageId %d not on tradepost', $storageId));
        }
        $tradePostId = $tradepost->getId();

        if ($selectedStorage->getAmount() < $amount) {
            $amount = $selectedStorage->getAmount();
        }
        if ($amount < 1) {
            return;
        }

        $usedTransferCapacity = $this->tradeTransferRepository->getSumByPostAndUser($tradePostId, $userId);
        $freeTransferCapacity = $tradepost->getTransferCapacity() - $usedTransferCapacity;

        if ($freeTransferCapacity <= 0) {
            $game->getInfo()->addInformation(_('Du hast an diesem Posten derzeit keine freie Transferkapaziztät'));
            return;
        }

        $targetpost = $this->tradePostRepository->find($destinationTradePostId);

        if ($targetpost === null || $targetpost->getUser()->getId() === UserConstants::USER_NOONE) {
            return;
        }

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, $tradePostId)) {
            return;
        }
        if ($targetpost->getTradeNetwork() !== $tradepost->getTradeNetwork()) {
            return;
        }

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradepost, $game->getUser());
        $storageManagerTarget = $this->tradeLibFactory->createTradePostStorageManager($targetpost, $game->getUser());

        $freeStorage = $storageManagerTarget->getFreeStorage();

        if ($freeStorage <= 0) {
            $game->getInfo()->addInformation(_('Am Zielposten ist kein freier Lagerplatz mehr vorhanden'));
            return;
        }
        $amount = min(min($freeStorage, $amount), $freeTransferCapacity);

        $storageManagerTarget->upperStorage($selectedStorage->getCommodityId(), $amount);
        $storageManager->lowerStorage($selectedStorage->getCommodityId(), $amount);

        $transfer = $this->tradeTransferRepository->prototype();
        $transfer->setTradePost($tradepost);
        $transfer->setUser($game->getUser());
        $transfer->setAmount($amount);
        $transfer->setDate(time());

        $this->tradeTransferRepository->save($transfer);

        $game->getInfo()->addInformation(
            sprintf(
                _('Es wurde %d %s zum %s transferiert'),
                $amount,
                $selectedStorage->getCommodity()->getName(),
                $targetpost->getName()
            )
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
