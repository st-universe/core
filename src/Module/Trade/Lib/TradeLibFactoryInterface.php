<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\BasicTradeInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\UserInterface;

interface TradeLibFactoryInterface
{
    public function createTradeAccountWrapper(
        TradePostInterface $tradePost,
        int $userId
    ): TradeAccountWrapperInterface;

    /** @param array<BasicTradeInterface> $basicTrades */
    public function createBasicTradeAccountWrapper(
        TradePostInterface $tradePost,
        array $basicTrades,
        int $userId
    ): BasicTradeAccountWrapperInterface;

    public function createTradePostStorageManager(
        TradePostInterface $tradePost,
        UserInterface $user
    ): TradePostStorageManagerInterface;
}
