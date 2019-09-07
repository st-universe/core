<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowAccounts;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use TradePost;

final class ShowAccounts implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ACCOUNTS';

    private $talFactory;

    public function __construct(
        TradeLibFactoryInterface $talFactory
    ) {
        $this->talFactory = $talFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $list = [];

        foreach (TradePost::getListByLicences($userId) as $account) {
            $list[] = $this->talFactory->createTradeAccountTal(
                $account,
                $userId
            );
        }

        $game->appendNavigationPart(
            sprintf('trade.php?%s=1', static::VIEW_IDENTIFIER),
            _('Warenkonten')
        );
        $game->setPageTitle(_('/ Handel'));
        $game->setTemplateFile('html/tradeaccounts.xhtml');

        $game->setTemplateVar('ACCOUNTS', $list);
    }
}