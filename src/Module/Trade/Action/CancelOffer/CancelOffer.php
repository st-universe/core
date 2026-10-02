<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CancelOffer;

use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class CancelOffer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CANCEL_OFFER';

    public function __construct(
        private CancelOfferRequestInterface $cancelOfferRequest,
        private TradeLibFactoryInterface $tradeLibFactory,
        private TradeOfferRepositoryInterface $tradeOfferRepository,
        private StorageRepositoryInterface $storageRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $viewIdentifier = $this->cancelOfferRequest->getView() ?? ModuleEnum::TRADE;
        $context->setView($viewIdentifier);
        $context->setViewContext(ViewContextMetadataTypeEnum::FILTER_ACTIVE, true);

        $userId = $context->getUser()->getId();
        $offerId = $this->cancelOfferRequest->getOfferId();

        /** @var TradeOffer $offer */
        $offer = $this->tradeOfferRepository->find($offerId);

        if ($offer->getUserId() !== $userId) {
            throw new AccessViolationException();
        }

        $this->tradeLibFactory->createTradePostStorageManager(
            $offer->getTradePost(),
            $context->getUser()
        )->upperStorage(
            $offer->getOfferedCommodityId(),
            $offer->getOfferedCommodityCount() * $offer->getOfferCount()
        );

        $this->storageRepository->delete($offer->getStorage());
        $this->tradeOfferRepository->delete($offer);

        $context->getInfo()->addInformation(_('Das Angebot wurde gelöscht'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
