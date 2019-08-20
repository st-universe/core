<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TakeOffer;

use PM;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use TradeOffer;
use TradePost;
use TradePostStorageWrapper;
use TradeStorage;

final class TakeOffer implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_TAKE_OFFER';

    private $takeOfferRequest;

    public function __construct(
        TakeOfferRequestInterface $takeOfferRequest
    ) {
        $this->takeOfferRequest = $takeOfferRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $offerId = $this->takeOfferRequest->getOfferId();
        $amount = $this->takeOfferRequest->getAmount();

        $selectedOffer = new TradeOffer($offerId);

        // @todo check if user may acces the offer (tradepost)

        if ($userId === $selectedOffer->getUserId()) {
            return;
        }

        $storage = TradeStorage::getStorageByGood(
            $selectedOffer->getTradePostId(),
            $userId,
            $selectedOffer->getWantedGoodId()
        );

        if (!$storage || $storage->getAmount() < $selectedOffer->getWantedGoodCount()) {
            $game->addInformation(sprintf(
                _('Es befindet sich nicht genügend %s auf diesem Handelsposten'),
                $selectedOffer->getWantedGoodObject()->getName()
            ));
            return;
        }

        $wrap = new TradePostStorageWrapper($storage->getTradePostId(), $userId);

        if (
            $wrap->getStorageSum() > $storage->getTradePost()->getStorage() &&
            $selectedOffer->getOfferedGoodCount() > $selectedOffer->getWantedGoodCount()
        ) {
            $game->addInformation(_('Dein Warenkonto auf diesem Handelsposten ist voll'));
            return;
        }
        if ($amount * $selectedOffer->getWantedGoodCount() > $storage->getAmount()) {
            $amount = floor($storage->getAmount() / $selectedOffer->getWantedGoodCount());
        }
        if ($amount * $selectedOffer->getOfferedGoodCount() - $amount * $selectedOffer->getWantedGoodCount() > $storage->getTradePost()->getStorage() - $wrap->getStorageSum()) {
            $amount = floor(($storage->getTradePost()->getStorage() - $wrap->getStorageSum()) / ($selectedOffer->getOfferedGoodCount() - $selectedOffer->getWantedGoodCount()));
            if ($amount <= 0) {
                $game->addInformation(_('Es steht für diese Transaktion nicht genügend Platz in deinem Warenkonto zur Verfügung'));
                return;
            }
        }

        if ($selectedOffer->getOfferCount() <= $amount) {
            $amount = $selectedOffer->getOfferCount();
            $selectedOffer->deleteFromDatabase();
        } else {
            $selectedOffer->lowerOfferCount($amount);
            $selectedOffer->save();
        }

        /**
         * @var TradePost $trade_post
         */
        $trade_post = $storage->getTradePost();

        $trade_post->upperStorage($selectedOffer->getUserId(), $selectedOffer->getWantedGoodId(), $selectedOffer->getWantedGoodCount() * $amount);
        $trade_post->upperStorage($userId, $selectedOffer->getOfferedGoodId(), $selectedOffer->getOfferedGoodCount() * $amount);
        $trade_post->lowerStorage($userId, $selectedOffer->getWantedGoodId(), $selectedOffer->getWantedGoodCount() * $amount);

        $game->addInformation(sprintf(_('Das Angebot wurde %d mal angenommen'), $amount));

        PM::sendPM(
            $userId,
            $selectedOffer->getUserId(),
            sprintf(
                'Es wurden insgesamt %d %s gegen %d %s getauscht',
                $selectedOffer->getOfferedGoodCount() * $amount,
                $selectedOffer->getOfferedGoodObject()->getName(),
                $selectedOffer->getWantedGoodCount() * $amount,
                $selectedOffer->getWantedGoodObject()->getName()
            ),
            PM_SPECIAL_TRADE
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
