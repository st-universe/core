<?php

declare(strict_types=1);

namespace Stu\Component\Trade;

final class TradeEnum
{
    // filter which type to show
    public const int FILTER_COMMODITY_IN_BOTH = 0;
    public const int FILTER_COMMODITY_IN_OFFER = 1;
    public const int FILTER_COMMODITY_IN_DEMAND = 2;

    // basic trade stuff
    public const int BASIC_TRADE_LATEST_RATE_AMOUNT = 5;
    public const int BASIC_TRADE_BUY_MODIFIER = -10;
    public const int BASIC_TRADE_SELL_MODIFIER = 10;

    // Deals
    public const int DEALS_FERG_TRADEPOST_ID = 2;
}
