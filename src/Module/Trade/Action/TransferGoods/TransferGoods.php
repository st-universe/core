<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TransferGoods;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;
use Stu\Orm\Repository\TradeTransferRepositoryInterface;

final class TransferGoods implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRANSFER';

    private TransferGoodsRequestInterface $transferGoodsRequest;

    private TradeTransferRepositoryInterface $tradeTransferRepository;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradePostRepositoryInterface $tradePostRepository;

    private TradeStorageRepositoryInterface $tradeStorageRepository;

    public function __construct(
        TransferGoodsRequestInterface $transferGoodsRequest,
        TradeTransferRepositoryInterface $tradeTransferRepository,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository
    ) {
        $this->transferGoodsRequest = $transferGoodsRequest;
        $this->tradeTransferRepository = $tradeTransferRepository;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowAccounts::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $amount = $this->transferGoodsRequest->getAmount();
        $destinationTradePostId = $this->transferGoodsRequest->getDestinationTradePostId();

        if ($destinationTradePostId == -1) {
            return;
        }

        $storageId = $this->transferGoodsRequest->getStorageId();
        $selectedStorage = $this->tradeStorageRepository->find($storageId);
        if ($selectedStorage === null) {
            throw new AccessViolation(sprintf(_('userId %d tried to transfer non-existent storageId %d'), $userId, $storageId));
        }
        if ($selectedStorage->getUserId() !== $userId) {
            throw new AccessViolation(sprintf(_('userId %d tried to transfer foreign storageId %d'), $userId, $storageId));
        }

        $tradepost = $selectedStorage->getTradePost();
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
            $game->addInformation(_('Du hast an diesem Posten derzeit keine freie Transferkapaziztät'));
            return;
        }

        $targetpost = $this->tradePostRepository->find($destinationTradePostId);

        if ($targetpost === null) {
            return;
        }

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, $tradePostId)) {
            return;
        }
        if ($targetpost->getTradeNetwork() != $tradepost->getTradeNetwork()) {
            return;
        }

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradepost, $userId);
        $storageManagerTarget = $this->tradeLibFactory->createTradePostStorageManager($targetpost, $userId);

        $freeStorage = $storageManager->getFreeStorage();

        if ($freeStorage <= 0) {
            return;
        }
        $amount = (int) min(min($freeStorage, $amount), $freeTransferCapacity);

        $storageManagerTarget->upperStorage((int) $selectedStorage->getGoodId(), $amount);
        $storageManager->lowerStorage((int) $selectedStorage->getGoodId(), $amount);

        $transfer = $this->tradeTransferRepository->prototype();
        $transfer->setTradePost($tradepost);
        $transfer->setUser($game->getUser());
        $transfer->setAmount($amount);
        $transfer->setDate(time());

        $this->tradeTransferRepository->save($transfer);

        $game->addInformation(
            sprintf(
                _('Es wurde %d %s zum %s transferiert'),
                $amount,
                $selectedStorage->getGood()->getName(),
                $targetpost->getName()
            )
        );
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
