<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowDeals;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowDeals implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_DEALS';

    private DealsRepositoryInterface $dealsRepository;

    public function __construct(
        DealsRepositoryInterface $dealsRepository,
        TradePostRepositoryInterface $tradePostRepository,
        TradeLibFactoryInterface $tradeLibFactory
    ) {
        $this->dealsRepository = $dealsRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->tradeLibFactory = $tradeLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $activedeals = $this->dealsRepository->getActiveDeals($userId);

        if (!empty($activedeals)) {
            $activedealsgoods = $this->dealsRepository->getActiveDealsGoods($userId);
            $activedealsships = $this->dealsRepository->getActiveDealsShips($userId);
            $activedealsbuildplans = $this->dealsRepository->getActiveDealsBuildplans($userId);
            $activedealsgoodsprestige = $this->dealsRepository->getActiveDealsGoodsPrestige($userId);
            $activedealsshipsprestige = $this->dealsRepository->getActiveDealsShipsPrestige($userId);
            $activedealsbuildplansprestige = $this->dealsRepository->getActiveDealsBuildplansPrestige($userId);
        }

        $activeauctions = $this->dealsRepository->getActiveAuctions($userId);
        if (!empty($activauctions)) {
            $activeauctionsgoods = $this->dealsRepository->getActiveAuctionsGoods($userId);
            $activeauctionsships = $this->dealsRepository->getActiveAuctionsShips($userId);
            $activeauctionsbuildplans = $this->dealsRepository->getActiveAuctionsBuildplans($userId);
            $activeauctionsgoodsprestige = $this->dealsRepository->getActiveAuctionsGoodsPrestige($userId);
            $activeauctionsshipsprestige = $this->dealsRepository->getActiveAuctionsShipsPrestige($userId);
            $activeauctionsbuildplansprestige = $this->dealsRepository->getActiveAuctionsBuildplansPrestige($userId);
        }

        $anyendedauctions = $this->dealsRepository->getAnyEndedAuctions($userId);
        if (!empty($anyendedauctions)) {
            $endedauctions = $this->dealsRepository->getEndedAuctions($userId);
            $ownendedauctions = $this->dealsRepository->getOwnEndedAuctions($userId);
            $endedauctionsgoods = $this->dealsRepository->getEndedAuctionsGoods($userId);
            $endedauctionsships = $this->dealsRepository->getEndedAuctionsShips($userId);
            $endedauctionsbuildplans = $this->dealsRepository->getEndedAuctionsBuildplans($userId);
            $endedauctionsgoodsprestige = $this->dealsRepository->getEndedAuctionsGoodsPrestige($userId);
            $endedauctionsshipsprestige = $this->dealsRepository->getEndedAuctionsShipsPrestige($userId);
            $endedauctionsbuildplansprestige = $this->dealsRepository->getEndedAuctionsBuildplansPrestige($userId);
            $ownendedauctionsgoods = $this->dealsRepository->getOwnEndedAuctionsGoods($userId);
            $ownendedauctionsships = $this->dealsRepository->getOwnEndedAuctionsShips($userId);
            $ownendedauctionsbuildplans = $this->dealsRepository->getOwnEndedAuctionsBuildplans($userId);
            $ownendedauctionsgoodsprestige = $this->dealsRepository->getOwnEndedAuctionsGoodsPrestige($userId);
            $ownendedauctionsshipsprestige = $this->dealsRepository->getOwnEndedAuctionsShipsPrestige($userId);
            $ownendedauctionsbuildplansprestige = $this->dealsRepository->getOwnEndedAuctionsBuildplansPrestige($userId);
        }

        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );
        $game->appendNavigationPart(
            sprintf('trade.php?%s=1', static::VIEW_IDENTIFIER),
            _('Deals')
        );
        $game->setPageTitle(_('/ Handel / Deals des Großen Nagus'));
        $game->setTemplateFile('html/deals.xhtml');

        $game->setTemplateVar('ACTIVEDEALS', $activedeals);
        $game->setTemplateVar('ACTIVEDEALSGOODS', $activedealsgoods);
        $game->setTemplateVar('ACTIVEDEALSSHIPS', $activedealsships);
        $game->setTemplateVar('ACTIVEDEALSBUILDPLANS', $activedealsbuildplans);
        $game->setTemplateVar('ACTIVEDEALSGOODSPRESTIGE', $activedealsgoodsprestige);
        $game->setTemplateVar('ACTIVEDEALSSHIPSPRESTIGE', $activedealsshipsprestige);
        $game->setTemplateVar('ACTIVEDEALSBUILDPLANSPRESTIGE', $activedealsbuildplansprestige);
        $game->setTemplateVar('ACTIVEAUCTIONS', $activeauctions);
        $game->setTemplateVar('ACTIVEAUCTIONSGOODS', $activeauctionsgoods);
        $game->setTemplateVar('ACTIVEAUCTIONSSHIPS', $activeauctionsships);
        $game->setTemplateVar('ACTIVEAUCTIONSBUILDPLANS', $activeauctionsbuildplans);
        $game->setTemplateVar('ACTIVEAUCTIONSGOODSPRESTIGE', $activeauctionsgoodsprestige);
        $game->setTemplateVar('ACTIVEAUCTIONSSHIPSPRESTIGE', $activeauctionsshipsprestige);
        $game->setTemplateVar('ACTIVEAUCTIONSBUILDPLANSPRESTIGE', $activeauctionsbuildplansprestige);
        $game->setTemplateVar('ANYENDEDAUCTIONS', $anyendedauctions);
        $game->setTemplateVar('ENDEDAUCTIONS', $endedauctions);
        $game->setTemplateVar('OWNENDEDAUCTIONS', $ownendedauctions);
        $game->setTemplateVar('ENDEDAUCTIONSGOODS', $endedauctionsgoods);
        $game->setTemplateVar('ENDEDAUCTIONSSHIPS', $endedauctionsships);
        $game->setTemplateVar('ENDEDAUCTIONSBUILDPLANS', $endedauctionsbuildplans);
        $game->setTemplateVar('ENDEDAUCTIONSGOODSPRESTIGE', $endedauctionsgoodsprestige);
        $game->setTemplateVar('ENDEDAUCTIONSSHIPSPRESTIGE', $endedauctionsshipsprestige);
        $game->setTemplateVar('ENDEDAUCTIONSBUILDPLANSPRESTIGE', $endedauctionsbuildplansprestige);
        $game->setTemplateVar('OWNENDEDAUCTIONSGOODS', $ownendedauctionsgoods);
        $game->setTemplateVar('OWNENDEDAUCTIONSSHIPS', $ownendedauctionsships);
        $game->setTemplateVar('OWNENDEDAUCTIONSBUILDPLANS', $ownendedauctionsbuildplans);
        $game->setTemplateVar('OWNENDEDAUCTIONSGOODSPRESTIGE', $ownendedauctionsgoodsprestige);
        $game->setTemplateVar('OWNENDEDAUCTIONSSHIPSPRESTIGE', $ownendedauctionsshipsprestige);
        $game->setTemplateVar('OWNENDEDAUCTIONSBUILDPLANSPRESTIGE', $ownendedauctionsbuildplansprestige);
    }
}