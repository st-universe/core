<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\SetBasicTradeChartLimit;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\View\ShowBasicTrade\ShowBasicTrade;

final class SetBasicTradeChartLimit implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_BASIC_TRADE_CHART_LIMIT';

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowBasicTrade::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
