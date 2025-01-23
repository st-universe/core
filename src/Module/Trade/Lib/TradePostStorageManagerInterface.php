<?php

namespace Stu\Module\Trade\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TradePostInterface;

interface TradePostStorageManagerInterface
{
    public function getTradePost(): TradePostInterface;

    public function getFreeStorage(): int;

    public function upperStorage(int $commodityId, int $amount): void;

    public function lowerStorage(int $commodityId, int $amount): void;

    /** @return Collection<int, StorageInterface> Indexed by commodityId, ordered by commodityId */
    public function getStorage(): Collection;

    public function getStorageSum(): int;
}
