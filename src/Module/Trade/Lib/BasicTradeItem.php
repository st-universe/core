<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\BasicTradeInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\TradeStorageInterface;

final class BasicTradeItem implements BasicTradeItemInterface
{
    private BasicTradeInterface $basicTrade;

    private TradeStorageInterface $tradeStorage;

    public function __construct(
        BasicTradeInterface $basicTrade,
        TradeStorageInterface $tradeStorage
    ) {
        $this->basicTrade = $basicTrade;
        $this->tradeStorage = $tradeStorage;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->basicTrade->getCommodity();
    }

    public function getStoredAmount(): int
    {
        return $this->tradeStorage->getAmount();
    }

    public function getBuyValue(): int
    {
        return $this->basicTrade->getValue();
    }

    public function getSellValue(): int
    {
        //TODO how to delta?
        return $this->basicTrade->getValue() + 10;
    }
}
