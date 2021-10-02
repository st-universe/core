<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowSearch;

use request;

use Stu\Component\Game\GameEnum;
use Stu\Component\Trade\TradeEnum;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\TradeOfferItem;
use Stu\Module\Trade\Lib\TradeOfferItemInterface;
use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class ShowSearchBoth implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'B_TRADE_SEARCH_BOTH';

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

        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );
        $game->setPageTitle(_('/ Handel'));
        $game->setTemplateFile('html/trade.xhtml');

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

        $commodityList = $this->commodityRepository->getViewable();
        $game->setTemplateVar('SELECTABLE_GOODS', $commodityList);

        $game->setTemplateVar('MAX_TRADE_LICENSE_COUNT', GameEnum::MAX_TRADELICENCE_COUNT);
        $game->setTemplateVar(
            'OFFER_LIST',
            array_map(
                function (TradeOfferInterface $tradeOffer) use ($user): TradeOfferItemInterface {
                    return new TradeOfferItem($tradeOffer, $user);
                },
                $this->tradeOfferRepository->getByUserLicenses($userId, $commodityId, $postId, TradeEnum::FILTER_COMMODITY_IN_BOTH)
            )
        );
    }
}
