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
        $activedealsgoods = $this->dealsRepository->getActiveDealsGoods($userId);
        $activedealsships = $this->dealsRepository->getActiveDealsShips($userId);
        $activedealsbuildplans = $this->dealsRepository->getActiveDealsBuildplans($userId);
        $activedealsgoodsprestige = $this->dealsRepository->getActiveDealsGoodsPrestige($userId);
        $activedealsshipsprestige = $this->dealsRepository->getActiveDealsShipsPrestige($userId);
        $activedealsbuildplansprestige = $this->dealsRepository->getActiveDealsBuildplansPrestige($userId);

        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );
        $game->appendNavigationPart(
            sprintf('trade.php?%s=1', static::VIEW_IDENTIFIER),
            _('Deals')
        );
        $game->setPageTitle(_('/ Handel'));
        $game->setTemplateFile('html/deals.xhtml');

        $game->setTemplateVar('ACTIVEDEALS', $activedeals);
        $game->setTemplateVar('ACTIVEDEALSGOODS', $activedealsgoods);
        $game->setTemplateVar('ACTIVEDEALSSHIPS', $activedealsships);
        $game->setTemplateVar('ACTIVEDEALSBUILDPLANS', $activedealsbuildplans);
        $game->setTemplateVar('ACTIVEDEALSGOODSPRESTIGE', $activedealsgoodsprestige);
        $game->setTemplateVar('ACTIVEDEALSSHIPSPRESTIGE', $activedealsshipsprestige);
        $game->setTemplateVar('ACTIVEDEALSBUILDPLANSPRESTIGE', $activedealsbuildplansprestige);
    }
}
