<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CancelOffer;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use TradeOffer;

final class CancelOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_OFFER';

    private $cancelOfferRequest;

    private $tradeLibFactory;

    public function __construct(
        CancelOfferRequestInterface $cancelOfferRequest,
        TradeLibFactoryInterface $tradeLibFactory
    ) {
        $this->cancelOfferRequest = $cancelOfferRequest;
        $this->tradeLibFactory = $tradeLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $offerId = $this->cancelOfferRequest->getOfferId();
        $offer = new TradeOffer($offerId);

        if ((int) $offer->getUserId() !== $userId) {
            new AccessViolation;
        }

        $this->tradeLibFactory->createTradePostStorageManager(
            $offer->getTradePost(),
            $userId
        )->upperStorage(
            (int) $offer->getOfferedGoodId(),
            (int) $offer->getOfferedGoodCount() * $offer->getOfferCount()
        );

        $offer->deleteFromDatabase();

        $game->addInformation(_('Das Angebot wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
