<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\Component;

use Stu\Component\Game\GameEnum;
use Stu\Component\Trade\TradeEnum;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\Component\ViewComponentProviderInterface;
use Stu\Module\Trade\Lib\TradeOfferItem;
use Stu\Module\Trade\Lib\TradeOfferItemInterface;
use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class TradeProvider implements ViewComponentProviderInterface
{
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

    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $isFilterActive = $game->getViewContext()['FILTER_ACTIVE'] ?? false;

        $commodityId = null;
        $postId = null;
        $dir = TradeEnum::FILTER_COMMODITY_IN_BOTH;
        if ($isFilterActive) {
            if ($this->session->getSessionValue('trade_filter_cid')) {
                $commodityId = $this->session->getSessionValue('trade_filter_cid');
            }
            if ($this->session->getSessionValue('trade_filter_pid')) {
                $postId = $this->session->getSessionValue('trade_filter_pid');
            }
            if ($this->session->getSessionValue('trade_filter_dir')) {
                $dir = $this->session->getSessionValue('trade_filter_dir');
            }
        } else {
            $this->session->deleteSessionData('trade_filter_cid');
            $this->session->deleteSessionData('trade_filter_pid');
            $this->session->deleteSessionData('trade_filter_dir');
        }

        $game->setTemplateVar('COMMODITY_ID', $commodityId ?? 0);
        $game->setTemplateVar('POST_ID', $postId ?? 0);

        $tradeLicenses = $this->tradeLicenseRepository->getLicensesCountbyUser($userId);
        $game->setTemplateVar('TRADE_LICENSES', $tradeLicenses);
        $game->setTemplateVar('TRADE_LICENSE_COUNT', count($tradeLicenses));

        $commodityList = $this->commodityRepository->getTradeable();
        $game->setTemplateVar('SELECTABLE_COMMODITIES', $commodityList);

        $game->setTemplateVar('MAX_TRADE_LICENSE_COUNT', GameEnum::MAX_TRADELICENSE_COUNT);
        $game->setTemplateVar(
            'OFFER_LIST',
            array_map(
                fn (TradeOfferInterface $tradeOffer): TradeOfferItemInterface => new TradeOfferItem($tradeOffer, $user),
                $this->tradeOfferRepository->getByUserLicenses($userId, $commodityId, $postId, $dir)
            )
        );
    }
}
