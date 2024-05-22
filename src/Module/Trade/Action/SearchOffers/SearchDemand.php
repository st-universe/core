<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\SearchOffers;

use request;

use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Component\Trade\TradeEnum;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Trade\Lib\TradeOfferItem;
use Stu\Module\Trade\Lib\TradeOfferItemInterface;
use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class SearchDemand implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRADE_SEARCH_DEMAND';

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private CommodityRepositoryInterface $commodityRepository;

    private SessionInterface $session;

    public function __construct(
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        CommodityRepositoryInterface $commodityRepository,
        SessionInterface $session
    ) {
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->commodityRepository = $commodityRepository;
        $this->session = $session;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $commodityId = request::postIntFatal('cid');
        $postId = request::postIntFatal('pid') > 0 ? request::postIntFatal('pid') : null;

        $game->setView(ModuleViewEnum::TRADE);
        $game->setViewContext(ViewContextTypeEnum::FILTER_ACTIVE, true);

        $game->setTemplateVar('POST_ID', request::postIntFatal('pid'));
        $game->setTemplateVar('COMMODITY_ID', $commodityId);


        $this->session->deleteSessionData('trade_filter_cid');
        $this->session->deleteSessionData('trade_filter_pid');
        $this->session->deleteSessionData('trade_filter_dir');

        $this->session->storeSessionData('trade_filter_cid', $commodityId, true);
        $this->session->storeSessionData('trade_filter_pid', $postId, true);
        $this->session->storeSessionData('trade_filter_dir', TradeEnum::FILTER_COMMODITY_IN_DEMAND, true);

        $tradeLicenses = $this->tradeLicenseRepository->getByUser($userId);
        $game->setTemplateVar('TRADE_LICENSES', $tradeLicenses);
        $game->setTemplateVar('TRADE_LICENSE_COUNT', count($tradeLicenses));

        $commodityList = $this->commodityRepository->getTradeable();
        $game->setTemplateVar('SELECTABLE_COMMODITIES', $commodityList);

        $game->setTemplateVar('MAX_TRADE_LICENSE_COUNT', GameEnum::MAX_TRADELICENSE_COUNT);
        $game->setTemplateVar(
            'OFFER_LIST',
            array_map(
                fn (TradeOfferInterface $tradeOffer): TradeOfferItemInterface => new TradeOfferItem($tradeOffer, $user),
                $this->tradeOfferRepository->getByUserLicenses($userId, $commodityId, $postId, TradeEnum::FILTER_COMMODITY_IN_DEMAND)
            )
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
