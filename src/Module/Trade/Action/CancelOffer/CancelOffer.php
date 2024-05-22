<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CancelOffer;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class CancelOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_OFFER';

    private CancelOfferRequestInterface $cancelOfferRequest;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private StorageRepositoryInterface $storageRepository;

    public function __construct(
        CancelOfferRequestInterface $cancelOfferRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        StorageRepositoryInterface $storageRepository
    ) {
        $this->cancelOfferRequest = $cancelOfferRequest;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->storageRepository = $storageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $viewIdentifier = $this->cancelOfferRequest->getView() ?? ModuleViewEnum::TRADE;
        $game->setView($viewIdentifier);
        $game->setViewContext(ViewContextTypeEnum::FILTER_ACTIVE, true);

        $userId = $game->getUser()->getId();
        $offerId = $this->cancelOfferRequest->getOfferId();

        /** @var TradeOfferInterface $offer */
        $offer = $this->tradeOfferRepository->find($offerId);

        if ($offer->getUserId() !== $userId) {
            new AccessViolation();
        }

        $this->tradeLibFactory->createTradePostStorageManager(
            $offer->getTradePost(),
            $game->getUser()
        )->upperStorage(
            $offer->getOfferedCommodityId(),
            $offer->getOfferedCommodityCount() * $offer->getOfferCount()
        );

        $this->storageRepository->delete($offer->getStorage());
        $this->tradeOfferRepository->delete($offer);

        $game->addInformation(_('Das Angebot wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
