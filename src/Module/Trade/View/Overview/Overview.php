<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\Overview;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use TradeLicences;
use TradeOffer;

final class Overview implements ViewControllerInterface
{

    public function __construct(
    ) {
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );
        $game->setPageTitle(_('/ Handel'));
        $game->setTemplateFile('html/trade.xhtml');

        $game->setTemplateVar(
            'TRADE_LICENSE_COUNT',
            TradeLicences::countInstances(sprintf('user_id = %d', $userId))
        );
        $game->setTemplateVar('MAX_TRADE_LICENSE_COUNT', MAX_TRADELICENCE_COUNT);
        $game->setTemplateVar('OFFER_LIST', TradeOffer::getByLicencedTradePosts($userId));
    }
}