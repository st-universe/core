<?php

namespace Stu\Module\Trade\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TradePost;

interface TradePostStorageManagerInterface
{
    public function getTradePost(): TradePost;

    public function getFreeStorage(): int;

    public function upperStorage(int $commodityId, int $amount): void;

    public function lowerStorage(int $commodityId, int $amount): void;

    /** @return Collection<int, Storage> Indexed by commodityId, ordered by commodityId */
    public function getStorage(): Collection;

    public function getStorageSum(): int;
}
