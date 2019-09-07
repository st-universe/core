<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\TradePostInterface;

interface TradeLibFactoryInterface
{
    public function createTradeAccountTal(
        TradePostInterface $tradePost,
        int $userId
    ): TradeAccountTalInterface;

    public function createTradePostStorageManager(
        TradePostInterface $tradePost,
        int $userId
    ): TradePostStorageManagerInterface;
}