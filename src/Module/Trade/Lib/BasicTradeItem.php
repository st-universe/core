<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Override;
use Stu\Orm\Entity\BasicTradeInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\StorageInterface;

final class BasicTradeItem implements BasicTradeItemInterface
{
    public const int BASIC_TRADE_VALUE_SCALE = 100;
    public const float BASIC_TRADE_SELL_BUY_ALPHA = 1.1;

    public function __construct(private ?BasicTradeInterface $basicTrade, private ?StorageInterface $storage, private ?CommodityInterface $commodity = null)
    {
    }

    #[Override]
    public function getUniqId(): string
    {
        return $this->basicTrade === null ? '' : $this->basicTrade->getUniqId();
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity ?? $this->basicTrade->getCommodity();
    }

    #[Override]
    public function getStoredAmount(): int
    {
        return $this->storage !== null ? $this->storage->getAmount() : 0;
    }

    #[Override]
    public function getBuyValue(): int
    {
        return (int) ($this->basicTrade->getValue() / self::BASIC_TRADE_VALUE_SCALE);
    }

    #[Override]
    public function getSellValue(): int
    {
        return (int)($this->basicTrade->getValue() / self::BASIC_TRADE_VALUE_SCALE * self::BASIC_TRADE_SELL_BUY_ALPHA);
    }
}
