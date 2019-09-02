<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TransferGoods;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use Stu\Orm\Repository\TradeTransferRepositoryInterface;
use TradePost;
use TradeStorage;

final class TransferGoods implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRANSFER';

    private $transferGoodsRequest;

    private $tradeTransferRepository;

    public function __construct(
        TransferGoodsRequestInterface $transferGoodsRequest,
        TradeTransferRepositoryInterface $tradeTransferRepository
    ) {
        $this->transferGoodsRequest = $transferGoodsRequest;
        $this->tradeTransferRepository = $tradeTransferRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowAccounts::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $amount = $this->transferGoodsRequest->getAmount();
        $destinationTradePostId = $this->transferGoodsRequest->getDestinationTradePostId();

        $selectedStorage = new TradeStorage($this->transferGoodsRequest->getStorageId());
        if ((int) $selectedStorage->getUserId() !== $userId) {
            throw new AccessViolation();
        }

        /**
         * @var TradePost $tradepost
         */
        $tradepost = $selectedStorage->getTradePost();

        if ($selectedStorage->getAmount() < $amount) {
            $amount = $selectedStorage->getAmount();
        }
        if ($amount < 1) {
            return;
        }

        if ($tradepost->getFreeTransferCapacity() <= 0) {
            $game->addInformation(_('Du hast an diesem Posten derzeit keine freie Transferkapaziztät'));
            return;
        }

        $targetpost = new TradePost($destinationTradePostId);

        if (!$targetpost->userHasLicence($userId)) {
            return;
        }
        if ($targetpost->getTradeNetwork() != $tradepost->getTradeNetwork()) {
            return;
        }
        if ($targetpost->getStorageSum() >= $targetpost->getStorage()) {
            return;
        }
        if ($amount + $targetpost->getStorageSum() > $targetpost->getStorage()) {
            $amount = $targetpost->getStorage() - $targetpost->getStorageSum();
        }
        if ($amount > $tradepost->getFreeTransferCapacity()) {
            $amount = $tradepost->getFreeTransferCapacity();
        }

        $targetpost->upperStorage($userId, $selectedStorage->getGoodId(), $amount);
        $tradepost->lowerStorage($userId, $selectedStorage->getGoodId(), $amount);

        $transfer = $this->tradeTransferRepository->prototype();
        $transfer->setTradePostId((int) $tradepost->getId());
        $transfer->setUserId($userId);
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
