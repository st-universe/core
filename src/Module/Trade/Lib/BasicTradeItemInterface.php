<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\CommodityInterface;

interface BasicTradeItemInterface
{
    public function getTradeStorageId(): ?int;

    public function getUniqId(): string;

    public function getCommodity(): CommodityInterface;

    public function getStoredAmount(): int;

    public function getBuyValue(): int;

    public function getSellValue(): int;
}
