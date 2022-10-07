<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\TradePostInterface;

interface TradePostStorageManagerInterface
{
    public function getTradePost(): TradePostInterface;

    public function getStorageSum(): int;

    public function getFreeStorage(): int;

    /**
     * @return StorageInterface[]
     */
    public function getStorage(): array;

    public function upperStorage(int $commodityId, int $amount): void;

    public function lowerStorage(int $commodityId, int $amount): void;
}
