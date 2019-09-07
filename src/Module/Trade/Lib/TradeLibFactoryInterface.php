<?php

namespace Stu\Module\Trade\Lib;

use TradePostData;

interface TradeLibFactoryInterface
{
    public function createTradeAccountTal(
        TradePostData $tradePost,
        int $userId
    ): TradeAccountTalInterface;

    public function createTradePostStorageManager(
        TradePostData $tradePost,
        int $userId
    ): TradePostStorageManagerInterface;
}