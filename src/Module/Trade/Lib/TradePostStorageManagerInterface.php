<?php

namespace Stu\Module\Trade\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TradePostInterface;

interface TradePostStorageManagerInterface
{
    public function getTradePost(): TradePostInterface;

    public function getStorageSum(): int;

    public function getFreeStorage(): int;

    /**
     * @return ArrayCollection<int, StorageInterface>
     */
    public function getStorage(): Collection;

    public function upperStorage(int $commodityId, int $amount): void;

    public function lowerStorage(int $commodityId, int $amount): void;
}
