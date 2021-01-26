<?php

namespace Stu\Module\Maintenance;

use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class OldTradeOffersDeletion implements MaintenanceHandlerInterface
{
    //two weeks
    public const OFFER_MAX_AGE = 1209600;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    public function __construct(
        TradeOfferRepositoryInterface $tradeOfferRepository,
        TradeLibFactoryInterface $tradeLibFactory
    ) {
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->tradeLibFactory = $tradeLibFactory;
    }

    public function handle(): void
    {
        $offersToDelete = $this->tradeOfferRepository->getOldOffers(OldTradeOffersDeletion::OFFER_MAX_AGE);

        foreach ($offersToDelete as $offer) {
            $this->tradeLibFactory->createTradePostStorageManager(
                $offer->getTradePost(),
                $offer->getUserId(),
            )->upperStorage(
                (int) $offer->getOfferedGoodId(),
                (int) $offer->getOfferedGoodCount() * $offer->getOfferCount()
            );

            $this->tradeOfferRepository->delete($offer);
        }
    }
}
