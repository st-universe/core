<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\UserInterface;

interface TradeLibFactoryInterface
{
    public function createTradeAccountTal(
        TradePostInterface $tradePost,
        int $userId
    ): TradeAccountTalInterface;

    public function createBasicTradeAccountTal(
        TradePostInterface $tradePost,
        array $basicTrades,
        int $userId
    ): BasicTradeAccountTalInterface;

    public function createTradePostStorageManager(
        TradePostInterface $tradePost,
        UserInterface $user
    ): TradePostStorageManagerInterface;
}
