<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TransferGoods;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use TradePost;
use TradeStorage;
use TradeTransfer;

final class TransferGoods implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_TRANSFER';

    private $transferGoodsRequest;

    public function __construct(
        TransferGoodsRequestInterface $transferGoodsRequest
    ) {
        $this->transferGoodsRequest = $transferGoodsRequest;
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

        if (!$targetpost->currentUserHasLicence()) {
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

        TradeTransfer::registerTransfer($tradepost->getId(), $userId, $amount);

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
