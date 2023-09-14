<?php

declare(strict_types=1);

namespace Stu\Component\Trade;

final class TradeEnum
{
    // filter which type to show
    public const FILTER_COMMODITY_IN_BOTH = 0;
    public const FILTER_COMMODITY_IN_OFFER = 1;
    public const FILTER_COMMODITY_IN_DEMAND = 2;

    // basic trade stuff
    public const BASIC_TRADE_LATEST_RATE_AMOUNT = 5;
    public const BASIC_TRADE_BUY_MODIFIER = -10;
    public const BASIC_TRADE_SELL_MODIFIER = 10;

    // Deals
    public const DEALS_FERG_TRADEPOST_ID = 59;
}
