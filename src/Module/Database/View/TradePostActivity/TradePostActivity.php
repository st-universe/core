<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\TradePostActivity;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseTopActivTradePost;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;

final class TradePostActivity implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TOP_ACTIV_TRADEPOST';

    public function __construct(private DatabaseUiFactoryInterface $databaseUiFactory, private TradeTransactionRepositoryInterface $tradeTransactionRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setNavigation([
            [
                'url' => 'database.php',
                'title' => 'Datenbank'
            ],
            [
                'url' => sprintf('database.php?%s=1', static::VIEW_IDENTIFIER),
                'title' => 'Die Top 10 der Handelsposten'
            ]
        ]);
        $game->setPageTitle(_('/ Datenbank / Die Top 10 der Handelsposten'));
        $game->showMacro('html/database.xhtml/top_activ_tradeposts');

        $game->setTemplateVar(
            'ACTIV_TRADEPOST',
            array_map(
                fn (array $data): DatabaseTopActivTradePost => $this->databaseUiFactory->createDatabaseTopActivTradePost($data),
                $this->tradeTransactionRepository->getTradePostsTop10()
            )
        );
    }
}
