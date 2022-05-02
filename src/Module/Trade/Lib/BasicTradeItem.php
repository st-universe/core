<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\BasicTradeInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\TradeStorageInterface;

final class BasicTradeItem implements BasicTradeItemInterface
{
    private ?BasicTradeInterface $basicTrade;

    private ?TradeStorageInterface $tradeStorage;

    public function __construct(
        ?BasicTradeInterface $basicTrade,
        ?TradeStorageInterface $tradeStorage
    ) {
        $this->basicTrade = $basicTrade;
        $this->tradeStorage = $tradeStorage;
    }

    public function getUniqId(): string
    {
        return $this->basicTrade->getUniqId();
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->tradeStorage !== null ? $this->tradeStorage->getCommodity()
            : $this->basicTrade->getCommodity();
    }

    public function getStoredAmount(): int
    {
        return $this->tradeStorage !== null ? $this->tradeStorage->getAmount() : 0;
    }

    public function getBuyValue(): int
    {
        return (int) ($this->basicTrade->getValue() / 100);
    }

    public function getSellValue(): int
    {
        //TODO how to delta?
        return (int)($this->basicTrade->getValue() / 100 * 1.1);
    }
}
