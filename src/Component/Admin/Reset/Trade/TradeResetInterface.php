<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Trade;

interface TradeResetInterface
{
    public function deleteAllBasicTrades(): void;

    public function deleteAllDeals(): void;

    public function deleteAllLotteryTickets(): void;

    public function deleteAllTradeShoutboxEntries(): void;

    public function deleteAllTradeTransactions(): void;
}
