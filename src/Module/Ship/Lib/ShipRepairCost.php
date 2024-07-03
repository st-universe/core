<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

final class ShipRepairCost
{
    public function __construct(private int $amount, private int $commodityId, private string $commodityName)
    {
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCommodityId(): int
    {
        return $this->commodityId;
    }

    public function getCommodityName(): string
    {
        return $this->commodityName;
    }
}
