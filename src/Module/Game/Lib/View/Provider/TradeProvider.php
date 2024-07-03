<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use Stu\Component\Game\GameEnum;
use Stu\Component\Trade\TradeEnum;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;
use Stu\Module\Trade\Lib\TradeOfferItem;
use Stu\Module\Trade\Lib\TradeOfferItemInterface;
use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class TradeProvider implements ViewComponentProviderInterface
{
    public function __construct(private TradeLicenseRepositoryInterface $tradeLicenseRepository, private TradeOfferRepositoryInterface $tradeOfferRepository, private CommodityRepositoryInterface $commodityRepository, private SessionInterface $session)
    {
    }

    #[Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $isFilterActive = $game->getViewContext(ViewContextTypeEnum::FILTER_ACTIVE) ?? false;

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
