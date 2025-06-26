<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\BasicTrade;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\User;

interface TradeLibFactoryInterface
{
    public function createTradeAccountWrapper(
        TradePost $tradePost,
        int $userId
    ): TradeAccountWrapperInterface;

    /** @param array<BasicTrade> $basicTrades */
    public function createBasicTradeAccountWrapper(
        TradePost $tradePost,
        array $basicTrades,
        int $userId
    ): BasicTradeAccountWrapperInterface;

    public function createTradePostStorageManager(
        TradePost $tradePost,
        User $user
    ): TradePostStorageManagerInterface;
}
