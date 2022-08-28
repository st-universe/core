<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\TradePostActivity;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseTopActivTradePost;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;

final class TradePostActivity implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_TOP_ACTIV_TRADEPOST';

    private TradeTransactionRepositoryInterface $tradeTransactionRepository;

    public function __construct(
        TradeTransactionRepositoryInterface $tradeTransactionRepository
    ) {
        $this->tradeTransactionRepository = $tradeTransactionRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            'database.php',
            _('Datenbank')
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Die Top 10 der Handelsposten')
        );
        $game->setPageTitle(_('/ Datenbank / Die Top 10 der Handelsposten'));
        $game->showMacro('html/database.xhtml/top_activ_tradeposts');

        $game->setTemplateVar('ACTIV_TRADEPOST', $this->getTop10());
    }

    private function getTop10()
    {
        return array_map(
            function (array $data): DatabaseTopActivTradePost {
                return new DatabaseTopActivTradePost($data);
            },
            $this->tradeTransactionRepository->getTradePostsTop10()
        );
    }
}