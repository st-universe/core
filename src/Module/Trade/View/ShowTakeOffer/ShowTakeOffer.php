<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTakeOffer;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class ShowTakeOffer implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TAKE_OFFER';

    public function __construct(private ShowTakeOfferRequestInterface $showTakeOfferRequest, private TradeLibFactoryInterface $tradeLibFactory, private TradeLicenseRepositoryInterface $tradeLicenseRepository, private TradeOfferRepositoryInterface $tradeOfferRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setMacroInAjaxWindow('html/trade/takeOffer.twig');
        $game->setPageTitle(_('Angebot annehmen'));

        $selectedOffer = $this->tradeOfferRepository->find($this->showTakeOfferRequest->getOfferId());

        if ($selectedOffer !== null && !$this->tradeLicenseRepository->hasLicenseByUserAndTradePost(
            $userId,
            $selectedOffer->getTradePostId()
        )) {
            throw new AccessViolation(sprintf(
                'userId %d does not have a license for tradePostId %d',
                $userId,
                $selectedOffer->getTradePostId()
            ));
        }

        if ($selectedOffer !== null) {
            $game->setTemplateVar(
                'SELECTED_OFFER',
                $selectedOffer
            );

            $tradeAccount = $this->tradeLibFactory->createTradeAccountWrapper($selectedOffer->getTradePost(), $game->getUser()->getId());
            $isStorageExistent = array_key_exists($selectedOffer->getWantedCommodityId(), $tradeAccount->getStorage());

            $game->setTemplateVar('TRADE_ACCOUNT', $tradeAccount);
            $game->setTemplateVar('STORED', $isStorageExistent ? $tradeAccount->getStorage()[$selectedOffer->getWantedCommodityId()]->getAmount() : null);
        }
    }
}
