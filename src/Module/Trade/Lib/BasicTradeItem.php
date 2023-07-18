<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\BasicTradeInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\StorageInterface;

final class BasicTradeItem implements BasicTradeItemInterface
{
    public const BASIC_TRADE_VALUE_SCALE = 100;
    public const BASIC_TRADE_SELL_BUY_ALPHA = 1.1;

    private ?BasicTradeInterface $basicTrade;

    private ?StorageInterface $storage;

    private ?CommodityInterface $commodity;

    public function __construct(
        ?BasicTradeInterface $basicTrade,
        ?StorageInterface $storage,
        ?CommodityInterface $commodity = null
    ) {
        $this->basicTrade = $basicTrade;
        $this->storage = $storage;
        $this->commodity = $commodity;
    }

    public function getUniqId(): string
    {
        return $this->basicTrade === null ? '' : $this->basicTrade->getUniqId();
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity ?? $this->basicTrade->getCommodity();
    }

    public function getStoredAmount(): int
    {
        return $this->storage !== null ? $this->storage->getAmount() : 0;
    }

    public function getBuyValue(): int
    {
        return (int) ($this->basicTrade->getValue() / self::BASIC_TRADE_VALUE_SCALE);
    }

    public function getSellValue(): int
    {
        return (int)($this->basicTrade->getValue() / self::BASIC_TRADE_VALUE_SCALE * self::BASIC_TRADE_SELL_BUY_ALPHA);
    }
}
