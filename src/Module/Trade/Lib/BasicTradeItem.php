<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use RuntimeException;
use Stu\Orm\Entity\BasicTrade;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Storage;

final class BasicTradeItem implements BasicTradeItemInterface
{
    public const int BASIC_TRADE_VALUE_SCALE = 100;
    public const float BASIC_TRADE_SELL_BUY_ALPHA = 1.1;

    public function __construct(private ?BasicTrade $basicTrade, private ?Storage $storage, private ?Commodity $commodity = null) {}

    #[\Override]
    public function getUniqId(): string
    {
        return $this->basicTrade?->getUniqId() ?? '';
    }

    #[\Override]
    public function getCommodity(): Commodity
    {
        return $this->commodity ?? $this->basicTrade?->getCommodity() ?? throw new RuntimeException('either commodity or basicTrade should be filled');
    }

    #[\Override]
    public function getStoredAmount(): int
    {
        return $this->storage !== null ? $this->storage->getAmount() : 0;
    }

    #[\Override]
    public function getBuyValue(): int
    {
        return (int) (($this->basicTrade?->getValue() ?? 0) / self::BASIC_TRADE_VALUE_SCALE);
    }

    #[\Override]
    public function getSellValue(): int
    {
        return (int)((($this->basicTrade?->getValue() ?? 0) / self::BASIC_TRADE_VALUE_SCALE) * self::BASIC_TRADE_SELL_BUY_ALPHA);
    }
}
