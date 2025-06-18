<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\SearchOffers;

use Override;
use request;

use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleEnum;
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

final class SearchBoth implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRADE_SEARCH_BOTH';

    public function __construct(private TradeLicenseRepositoryInterface $tradeLicenseRepository, private TradeOfferRepositoryInterface $tradeOfferRepository, private CommodityRepositoryInterface $commodityRepository, private SessionInterface $session) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $commodityId = request::postIntFatal('cid');
        $postId = request::postIntFatal('pid') > 0 ? request::postIntFatal('pid') : null;

        $game->setView(ModuleEnum::TRADE);
        $game->setViewContext(ViewContextTypeEnum::FILTER_ACTIVE, true);

        $game->setTemplateVar('POST_ID', request::postIntFatal('pid'));
        $game->setTemplateVar('COMMODITY_ID', $commodityId);

        $this->session->deleteSessionData('trade_filter_cid');
        $this->session->deleteSessionData('trade_filter_pid');
        $this->session->deleteSessionData('trade_filter_dir');

        $this->session->storeSessionData('trade_filter_cid', $commodityId, true);
        $this->session->storeSessionData('trade_filter_pid', $postId, true);
        $this->session->storeSessionData('trade_filter_dir', TradeEnum::FILTER_COMMODITY_IN_BOTH, true);

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
                $this->tradeOfferRepository->getByUserLicenses($userId, $commodityId, $postId, TradeEnum::FILTER_COMMODITY_IN_BOTH)
            )
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
