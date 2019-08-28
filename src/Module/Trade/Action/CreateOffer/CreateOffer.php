<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CreateOffer;

use AccessViolation;
use Good;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use TradeOfferData;
use TradePost;
use TradeStorage;

final class CreateOffer implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_CREATE_OFFER';

    private $createOfferRequest;

    public function __construct(
        CreateOfferRequestInterface $createOfferRequest
    ) {
        $this->createOfferRequest = $createOfferRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $storage = new TradeStorage($this->createOfferRequest->getStorageId());
        if ((int) $storage->getUserId() !== $userId) {
            throw new AccessViolation();
        }
        $trade_post = new TradePost($storage->getTradePostId());

        $giveGoodId = $this->createOfferRequest->getGiveGoodId();
        $giveAmount = $this->createOfferRequest->getGiveAmount();
        $wantedGoodId = $this->createOfferRequest->getWantedGoodId();
        $wantedAmount = $this->createOfferRequest->getWantedAmount();
        $offerAmount = $this->createOfferRequest->getOfferAmount();

        if ($giveGoodId === $wantedGoodId) {
            return;
        }
        if ($giveAmount < 1 || $wantedAmount < 1) {
            return;
        }
        if ($trade_post->getStorageSum() > $trade_post->getStorage()) {
            $game->setView(ShowAccounts::VIEW_IDENTIFIER);
            $game->addInformation("Dein Warenkonto auf diesem Handelsposten ist überfüllt - Angebot kann nicht erstellt werden");
            return;
        }

        $selectable_goods = Good::getGoodsBy(sprintf(
            'WHERE view=1 AND tradeable=1 AND illegal_%d=0 ORDER BY sort',
            $trade_post->getTradeNetwork()
        ));

        if ($giveGoodId == GOOD_DILITHIUM) {
            if (!array_key_exists($wantedGoodId, $selectable_goods)) {
                return;
            }
        } else {
            if ($wantedGoodId != GOOD_DILITHIUM) {
                return;
            }
        }
        if ($offerAmount < 1 || $offerAmount > 99) {
            $offerAmount = 1;
        }
        if ($offerAmount * $giveAmount > $storage->getAmount()) {
            $offerAmount = floor($storage->getAmount() / $giveAmount);
        }
        if ($offerAmount < 1) {
            return;
        }
        $offer = new TradeOfferData();
        $offer->setUserId($userId);
        $offer->setTradePostId($storage->getTradePostId());
        $offer->setDate(time());
        $offer->setOfferedGoodId($giveGoodId);
        $offer->setOfferedGoodCount($giveAmount);
        $offer->setWantedGoodId($wantedGoodId);
        $offer->setWantedGoodCount($wantedAmount);
        $offer->setOfferCount($offerAmount);
        $offer->save();

        if ($storage->getAmount() <= $offerAmount * $giveAmount) {
            $storage->deleteFromDatabase();
        } else {
            $storage->lowerCount($offerAmount * $giveAmount);
            $storage->save();
        }

        $game->addInformation('Das Angebot wurde erstellt');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
