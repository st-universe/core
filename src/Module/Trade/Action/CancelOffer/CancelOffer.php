<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CancelOffer;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class CancelOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_OFFER';

    private CancelOfferRequestInterface $cancelOfferRequest;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    public function __construct(
        CancelOfferRequestInterface $cancelOfferRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        TradeOfferRepositoryInterface $tradeOfferRepository
    ) {
        $this->cancelOfferRequest = $cancelOfferRequest;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradeOfferRepository = $tradeOfferRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $offerId = $this->cancelOfferRequest->getOfferId();

        /** @var TradeOfferInterface $offer */
        $offer = $this->tradeOfferRepository->find($offerId);

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

        $this->tradeOfferRepository->delete($offer);

        $game->addInformation(_('Das Angebot wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
