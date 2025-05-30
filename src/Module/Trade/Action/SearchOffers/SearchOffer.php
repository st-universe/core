<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\SearchOffers;

use Override;
use request;

use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Trade\TradeEnum;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Trade\Lib\TradeOfferItem;
use Stu\Module\Trade\Lib\TradeOfferItemInterface;
use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class SearchOffer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRADE_SEARCH_OFFER';

    public function __construct(
        private readonly TradeLicenseRepositoryInterface $tradeLicenseRepository,
        private readonly TradeOfferRepositoryInterface $tradeOfferRepository,
        private readonly CommodityRepositoryInterface $commodityRepository,
        private readonly SessionStorageInterface $sessionStorage
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $commodityId = request::postIntFatal('cid');
        $postId = request::postIntFatal('pid') > 0 ? request::postIntFatal('pid') : null;

        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );

        $game->setView(ModuleEnum::TRADE);
        $game->setViewContext(ViewContextTypeEnum::FILTER_ACTIVE, true);

        $game->setTemplateVar('POST_ID', request::postIntFatal('pid'));
        $game->setTemplateVar('COMMODITY_ID', $commodityId);

        $this->sessionStorage->deleteSessionData('trade_filter_cid');
        $this->sessionStorage->deleteSessionData('trade_filter_pid');
        $this->sessionStorage->deleteSessionData('trade_filter_dir');

        $this->sessionStorage->storeSessionData('trade_filter_cid', $commodityId, true);
        $this->sessionStorage->storeSessionData('trade_filter_pid', $postId, true);
        $this->sessionStorage->storeSessionData('trade_filter_dir', TradeEnum::FILTER_COMMODITY_IN_OFFER, true);

        $tradeLicenses = $this->tradeLicenseRepository->getByUser($userId);
        $game->setTemplateVar('TRADE_LICENSES', $tradeLicenses);
        $game->setTemplateVar('TRADE_LICENSE_COUNT', count($tradeLicenses));

        $commodityList = $this->commodityRepository->getTradeable();
        $game->setTemplateVar('SELECTABLE_COMMODITIES', $commodityList);

        $game->setTemplateVar('MAX_TRADE_LICENSE_COUNT', GameEnum::MAX_TRADELICENSE_COUNT);
        $game->setTemplateVar(
            'OFFER_LIST',
            array_map(
                fn(TradeOfferInterface $tradeOffer): TradeOfferItemInterface => new TradeOfferItem($tradeOffer, $user),
                $this->tradeOfferRepository->getByUserLicenses($userId, $commodityId, $postId, TradeEnum::FILTER_COMMODITY_IN_OFFER)
            )
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
