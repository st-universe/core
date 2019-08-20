<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowAccounts;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use TradePost;

final class ShowAccounts implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_ACCOUNTS';

    public function __construct(
    ) {
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->appendNavigationPart(
            sprintf('trade.php?%s=1', static::VIEW_IDENTIFIER),
            _('Warenkonten')
        );
        $game->setPageTitle(_('/ Handel'));
        $game->setTemplateFile('html/tradeaccounts.xhtml');

        $game->setTemplateVar(
            'ACCOUNTS',
            TradePost::getListByLicences($userId)
        );
    }
}