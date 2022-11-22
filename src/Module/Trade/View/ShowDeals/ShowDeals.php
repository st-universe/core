<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowDeals;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\DealsInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\Lib\DealsItem;
use Stu\Module\Trade\Lib\DealsItemInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowDeals implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_DEALS';

    private DealsRepositoryInterface $dealsRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

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

        $deals = $this->dealsRepository->getDeals($userId);

        $dealAccounts = array_map(
            function (DealsInterface $deals) use ($userId): DealsItemInterface {
                return new DealsItem($deals, $userId);
            },
            $this->tradePostRepository->getByUserLicenseOnlyFerg($userId)
        );

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

        $game->setTemplateVar('ACCOUNTS', $dealAccounts);
    }
}